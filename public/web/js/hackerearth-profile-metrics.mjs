#!/usr/bin/env node

const handle = process.argv[2];
const timeoutMs = Number(process.argv[3] || 25000);
const browserName = (process.argv[4] || 'chromium').toLowerCase();

if (!handle) {
  console.log(JSON.stringify({ ok: false, error: 'Missing handle argument' }));
  process.exit(1);
}

const toInt = (value) => {
  if (value === null || value === undefined) {
    return null;
  }

  const normalized = String(value).replace(/,/g, '').trim();
  if (!/^\d+$/.test(normalized)) {
    return null;
  }

  return Number.parseInt(normalized, 10);
};

const normalizeMetrics = (value) => {
  if (!value || typeof value !== 'object') {
    return null;
  }

  const candidate = {
    points: toInt(value.points),
    contest_rating: toInt(value.contest_rating),
    problem_solved: toInt(value.problem_solved),
    solutions_submitted: toInt(value.solutions_submitted),
    global_rank: toInt(value.global_rank ?? value.rank),
    country_rank: toInt(value.country_rank),
  };

  if (
    candidate.problem_solved === null
    && candidate.solutions_submitted === null
    && candidate.contest_rating === null
    && candidate.global_rank === null
    && candidate.country_rank === null
  ) {
    return null;
  }

  return candidate;
};

const scanObjectForMetrics = (input) => {
  const queue = [input];
  const visited = new Set();

  while (queue.length > 0) {
    const current = queue.shift();
    if (!current || typeof current !== 'object') {
      continue;
    }

    if (visited.has(current)) {
      continue;
    }
    visited.add(current);

    const normalized = normalizeMetrics(current);
    if (normalized) {
      return normalized;
    }

    if (Array.isArray(current)) {
      for (const child of current) {
        queue.push(child);
      }
      continue;
    }

    for (const key of Object.keys(current)) {
      queue.push(current[key]);
    }
  }

  return null;
};

const parseSolvedFromDom = async (page) => {
  return await page.evaluate(() => {
    const parseNumber = (value) => {
      if (!value) return null;
      const normalized = value.replace(/,/g, '').trim();
      if (!/^\d+$/.test(normalized)) return null;
      return Number.parseInt(normalized, 10);
    };

    const bodyText = (document.body?.innerText || '').replace(/\s+/g, ' ');
    const bodyPatterns = [
      /([0-9][0-9,]*)\s+(?:Problems?|Problem)\s+Solved/i,
      /(?:Problems?|Problem)\s+Solved\s*[:\-]?\s*([0-9][0-9,]*)/i,
      /Solved\s+Problems?\s*[:\-]?\s*([0-9][0-9,]*)/i,
    ];

    for (const pattern of bodyPatterns) {
      const match = bodyText.match(pattern);
      if (match) {
        const parsed = parseNumber(match[1]);
        if (parsed !== null) {
          return parsed;
        }
      }
    }

    const nodes = Array.from(document.querySelectorAll('*'));
    const labelPattern = /^(?:problems?|problem)\s+solved$/i;
    const target = nodes.find((el) => labelPattern.test((el.textContent || '').trim()));
    if (!target) {
      return null;
    }

    const parentText = (target.parentElement?.textContent || '').replace(/\s+/g, ' ');
    const parentMatch = parentText.match(/([0-9][0-9,]*)\s+(?:Problems?|Problem)\s+Solved/i);
    if (parentMatch) {
      return parseNumber(parentMatch[1]);
    }

    let sibling = target.parentElement?.previousElementSibling;
    while (sibling) {
      const n = parseNumber((sibling.textContent || '').trim());
      if (n !== null) {
        return n;
      }
      sibling = sibling.previousElementSibling;
    }

    sibling = target.parentElement?.nextElementSibling;
    while (sibling) {
      const n = parseNumber((sibling.textContent || '').trim());
      if (n !== null) {
        return n;
      }
      sibling = sibling.nextElementSibling;
    }

    return null;
  });
};

const parseRanksFromDom = async (page) => {
  return await page.evaluate(() => {
    const parseNumber = (value) => {
      if (!value) return null;
      const normalized = value.replace(/,/g, '').trim();
      if (!/^\d+$/.test(normalized)) return null;
      return Number.parseInt(normalized, 10);
    };

    const text = document.body?.innerText || '';

    const findRank = (label) => {
      const regex = new RegExp(`([0-9][0-9,]*)\\s+${label}`, 'i');
      const match = text.match(regex);
      if (match) {
        return parseNumber(match[1]);
      }
      return null;
    };

    return {
      global_rank: findRank('Global\\s+Rank'),
      country_rank: findRank('Country\\s+Rank'),
    };
  });
};

async function run() {
  let playwright;
  try {
    playwright = await import('playwright');
  } catch (error) {
    console.log(JSON.stringify({
      ok: false,
      error: 'Playwright package is not installed. Run: npm install --save-dev playwright && npx playwright install chromium',
    }));
    process.exit(1);
  }

  const browserType = playwright[browserName] || playwright.chromium;
  const browser = await browserType.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  try {
    const context = await browser.newContext({
      userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
      viewport: { width: 1366, height: 768 },
    });

    const page = await context.newPage();
    let metrics = null;

    page.on('response', async (response) => {
      if (metrics) {
        return;
      }

      const contentType = response.headers()['content-type'] || '';
      if (!contentType.includes('application/json')) {
        return;
      }

      try {
        const json = await response.json();
        const found = scanObjectForMetrics(json);
        if (found && found.problem_solved !== null) {
          metrics = found;
        }
      } catch (_) {
        // Ignore non-JSON responses
      }
    });

    await page.goto(`https://www.hackerearth.com/@${handle}/`, {
      waitUntil: 'domcontentloaded',
      timeout: timeoutMs,
    });

    try {
      await page.waitForLoadState('networkidle', { timeout: Math.min(timeoutMs, 15000) });
    } catch (_) {
      // Continue with best effort
    }

    if (!metrics) {
      await page.waitForTimeout(1500);
    }

    if (!metrics) {
      const domSolved = await parseSolvedFromDom(page);
      const domRanks = await parseRanksFromDom(page);

      if (domSolved !== null || domRanks.global_rank !== null || domRanks.country_rank !== null) {
        metrics = {
          problem_solved: domSolved,
          solutions_submitted: null,
          contest_rating: null,
          points: null,
          global_rank: domRanks.global_rank,
          country_rank: domRanks.country_rank,
        };
      }
    }

    if (!metrics) {
      console.log(JSON.stringify({
        ok: false,
        error: 'Could not extract profile metrics from rendered page',
      }));
      process.exit(1);
    }

    console.log(JSON.stringify({
      ok: true,
      metrics,
    }));
  } finally {
    await browser.close();
  }
}

run().catch((error) => {
  console.log(JSON.stringify({ ok: false, error: error?.message || 'Unknown error' }));
  process.exit(1);
});
