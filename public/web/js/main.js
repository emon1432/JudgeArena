/**
 * JudgeArena Main Application Script
 * ----------------------------------
 * Handles core UI interactivity:
 * 1. Theme Controller (Light/Dark Mode Toggle with Storage Persistence)
 * 2. Tab Navigation System
 */

/* ==========================================================================
   1. THEME CONTROLLER
   ========================================================================== */
const STORAGE_KEY = "judgearena-theme";
const root = document.documentElement;

/**
 * Retrieves initial theme selection from localStorage, defaulting to 'light'.
 * @returns {string} 'light' or 'dark'
 */
function getInitialTheme() {
    try {
        const savedTheme = localStorage.getItem(STORAGE_KEY);
        if (savedTheme === "dark" || savedTheme === "light") {
            return savedTheme;
        }
    } catch (error) {
        console.warn("Storage not accessible:", error);
    }
    return "light"; // Standard default theme mode
}

/**
 * Applies the specified theme attribute to the root document element
 * and updates the toggle button icon.
 * @param {string} theme - Theme name ('light' | 'dark')
 * @param {boolean} savePreference - Whether to persist choice to localStorage
 */
function applyTheme(theme, savePreference = true) {
    root.setAttribute("data-theme", theme);
    root.setAttribute("data-bs-theme", theme);

    const themeIcon = document.getElementById("theme-toggle-icon");
    if (themeIcon) {
        themeIcon.className =
            theme === "dark" ? "fa-solid fa-moon" : "fa-solid fa-sun";
    }

    const themeSwitch = document.getElementById("theme-switch-checkbox");
    if (themeSwitch) {
        themeSwitch.checked = theme === "dark";
    }

    if (savePreference) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            console.warn("Storage not accessible:", error);
        }
    }
}

// Immediate theme execution on script load to prevent visual flashing
let currentTheme = getInitialTheme();
applyTheme(currentTheme, false);

document.addEventListener("DOMContentLoaded", () => {
    // Initialize Select2 on all select elements if the plugin is available
    if (typeof $ !== "undefined" && $.fn.select2) {
        $("select").select2({
            width: "100%",
            minimumResultsForSearch: 4,
        });
    }

    // Synchronize icon state on DOM load
    applyTheme(currentTheme, false);

    // Attach Theme Toggle Button Event Listener
    const themeToggleBtn = document.getElementById("theme-toggle-btn");
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", () => {
            const activeTheme = root.getAttribute("data-theme") || currentTheme;
            currentTheme = activeTheme === "dark" ? "light" : "dark";
            applyTheme(currentTheme, true);
        });
    }
});

/* ==========================================================================
   2. TAB NAVIGATION SYSTEM
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            // Deactivate all tab buttons
            tabButtons.forEach((b) => b.classList.remove("active"));

            // Activate clicked tab button
            btn.classList.add("active");

            // Hide all tab content panes
            tabContents.forEach((content) => content.classList.add("d-none"));

            // Show target tab content pane
            const targetPaneId = "tab-content-" + btn.dataset.tab;
            const targetPane = document.getElementById(targetPaneId);
            if (targetPane) {
                targetPane.classList.remove("d-none");
            }
        });
    });
});

/* ==========================================================================
   X. SCROLL TO TOP BUTTON
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const scrollToTopBtn = document.getElementById("scrollToTopBtn");

    if (scrollToTopBtn) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.opacity = "1";
                scrollToTopBtn.style.visibility = "visible";
                scrollToTopBtn.style.transform = "translateY(0)";
            } else {
                scrollToTopBtn.style.opacity = "0";
                scrollToTopBtn.style.visibility = "hidden";
                scrollToTopBtn.style.transform = "translateY(20px)";
            }
        });

        scrollToTopBtn.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        if(typeof initPlatformTableControls === 'function') {
            initPlatformTableControls();
        }
    }, 100);
});

/* ---------- Platform Directory Search & Filter Controls ---------- */
function initPlatformTableControls() {
    const searchInput = document.getElementById("platform-search-input");
    const filterPills = document.querySelectorAll(".platform-filter-pill");
    const sortOptions = document.querySelectorAll(".sort-option");
    const sortLabel = document.getElementById("current-sort-label");
    const tableRows = document.querySelectorAll(
        ".saas-directory-table tbody tr",
    );

    // 1. Keyboard Shortcut (⌘K or Ctrl+K)
    document.addEventListener("keydown", (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === "k") {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
    });

    // 2. Real-time Search Filtering
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });
    }

    // 3. Filter Pills Switch
    filterPills.forEach((pill) => {
        pill.addEventListener("click", () => {
            filterPills.forEach((p) => p.classList.remove("active"));
            pill.classList.add("active");
            const filterType = pill.getAttribute("data-filter");

            tableRows.forEach((row) => {
                if (filterType === "all") {
                    row.style.display = "";
                } else if (filterType === "active") {
                    row.style.display =
                        row.textContent.includes("Synced") ||
                        row.textContent.includes("ago")
                            ? ""
                            : "none";
                } else if (filterType === "top-rated") {
                    const ratingText = row.children[2]?.textContent || "";
                    const rating = parseInt(ratingText.replace(/,/g, ""), 10);
                    row.style.display =
                        !isNaN(rating) && rating >= 2000 ? "" : "none";
                } else if (filterType === "needs-sync") {
                    row.style.display =
                        row.textContent.includes("Pending") ||
                        row.textContent.includes("Never")
                            ? ""
                            : "none";
                }
            });
        });
    });

    // 4. Sort Dropdown Options
    sortOptions.forEach((opt) => {
        opt.addEventListener("click", (e) => {
            e.preventDefault();
            sortOptions.forEach((o) => o.classList.remove("active"));
            opt.classList.add("active");
            if (sortLabel) sortLabel.textContent = `Sort: ${opt.textContent}`;

            const sortType = opt.getAttribute("data-sort");
            const tbody = document.querySelector(".saas-directory-table tbody");
            if (!tbody) return;

            const rowsArray = Array.from(tbody.querySelectorAll("tr"));
            rowsArray.sort((a, b) => {
                if (sortType === "name") {
                    const nameA = a.children[0].textContent
                        .trim()
                        .toLowerCase();
                    const nameB = b.children[0].textContent
                        .trim()
                        .toLowerCase();
                    return nameA.localeCompare(nameB);
                } else if (sortType === "rating") {
                    const ratA =
                        parseInt(
                            a.children[2].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    const ratB =
                        parseInt(
                            b.children[2].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    return ratB - ratA;
                } else if (sortType === "solved") {
                    const solA =
                        parseInt(
                            a.children[4].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    const solB =
                        parseInt(
                            b.children[4].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    return solB - solA;
                } else {
                    return 0;
                }
            });

            rowsArray.forEach((r) => tbody.appendChild(r));
        });
    });
}

/* ---------- Share Profile Helper Functions ---------- */
function copyProfileUrl() {
    const urlInput = document.getElementById("share-profile-url-input");
    if (urlInput) {
        navigator.clipboard.writeText(urlInput.value);
        const toast = document.getElementById("copy-success-toast");
        const icon = document.getElementById("copy-btn-icon");
        const btnText = document.getElementById("copy-btn-text");
        if (toast) toast.classList.remove("d-none");
        if (icon) icon.className = "fa-solid fa-check";
        if (btnText) btnText.textContent = "Copied!";
        setTimeout(() => {
            if (toast) toast.classList.add("d-none");
            if (icon) icon.className = "fa-regular fa-copy";
            if (btnText) btnText.textContent = "Copy Link";
        }, 2500);
    }
}

function copyMarkdownBadge() {
    const badgeInput = document.getElementById("share-badge-markdown-input");
    if (badgeInput) {
        navigator.clipboard.writeText(badgeInput.value);
        alert("GitHub README Markdown badge copied to clipboard!");
    }
}



/* ==========================================================================
   6. AUTHENTICATION & PASSWORD UTILITIES ENGINE
   ========================================================================== */

/**
 * Toggles visibility of password input fields.
 * @param {string} inputId - ID of password input element
 * @param {HTMLElement} btn - Toggle button element
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    const icon = btn.querySelector("i");
    if (icon) {
        icon.className = isPassword
            ? "fa-regular fa-eye-slash"
            : "fa-regular fa-eye";
    }
}

/**
 * Evaluates password strength and updates visual progress bar.
 * @param {string} password - Raw password input text
 */
function checkPasswordStrength(password) {
    const bar = document.getElementById("password-strength-bar");
    const text = document.getElementById("password-strength-text");

    if (!bar || !text) return;

    let score = 0;
    if (!password) {
        bar.style.width = "0%";
        bar.className = "progress-bar bg-secondary";
        text.textContent = "Too short";
        text.className = "text-secondary";
        return;
    }

    if (password.length >= 8) score += 25;
    if (/[A-Z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 25;
    if (/[^A-Za-z0-9]/.test(password)) score += 25;

    bar.style.width = `${score}%`;

    if (score <= 25) {
        bar.className = "progress-bar bg-danger";
        text.textContent = "Weak";
        text.className = "text-danger fw-semibold";
    } else if (score <= 50) {
        bar.className = "progress-bar bg-warning";
        text.textContent = "Medium";
        text.className = "text-warning fw-semibold";
    } else if (score <= 75) {
        bar.className = "progress-bar bg-info";
        text.textContent = "Good";
        text.className = "text-info fw-semibold";
    } else {
        bar.className = "progress-bar bg-success";
        text.textContent = "Strong";
        text.className = "text-success fw-semibold";
    }
}

/**
 * Simulates password reset request and redirects to OTP verification page.
 * @param {Event} e - Form submit event
 */
function handlePasswordResetRequest(e) {
    e.preventDefault();

    const alertBox = document.getElementById("reset-alert-success");
    const btn = document.getElementById("reset-submit-btn");

    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending Code...';
    }

    setTimeout(() => {
        if (alertBox) alertBox.classList.remove("d-none");
        setTimeout(() => {
            window.location.href = "verify-otp.html";
        }, 800);
    }, 800);
}

/**
 * Auto-focus navigation for 6-digit OTP code inputs.
 */
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("otp-inputs-container");
    if (!container) return;

    const inputs = Array.from(container.querySelectorAll(".otp-field"));

    inputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            const value = e.target.value;
            if (value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Start OTP Resend Countdown Timer
    startOTPCountdown();
});

/**
 * Manages OTP resend countdown timer.
 */
let otpTimerInterval = null;
function startOTPCountdown() {
    let seconds = 45;
    const timerSpan = document.getElementById("resend-timer-count");
    const resendBtn = document.getElementById("resend-otp-btn");

    if (!timerSpan || !resendBtn) return;

    resendBtn.disabled = true;

    if (otpTimerInterval) clearInterval(otpTimerInterval);

    otpTimerInterval = setInterval(() => {
        seconds--;
        if (timerSpan) timerSpan.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(otpTimerInterval);
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.innerHTML =
                    '<i class="fa-solid fa-rotate-right me-1"></i> Resend Code';
            }
        }
    }, 1000);
}

/**
 * Resends OTP verification code.
 */
function resendOTPCode() {
    const resendBtn = document.getElementById("resend-otp-btn");
    if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.innerHTML =
            'Resend Code (<span id="resend-timer-count">45</span>s)';
    }
    alert(
        "A new 6-digit verification code has been sent to your email address!",
    );
    startOTPCountdown();
}

/**
 * Simulates OTP verification and redirects to reset-password.html.
 * @param {Event} e - Form submit event
 */
function handleVerifyOTP(e) {
    e.preventDefault();

    const btn = document.getElementById("verify-otp-btn");
    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying...';
    }

    setTimeout(() => {
        window.location.href = "reset-password.html";
    }, 900);
}

/**
 * Simulates setting a new password and redirects to login.html.
 * @param {Event} e - Form submit event
 */
function handleSetNewPassword(e) {
    e.preventDefault();

    const pass = document.getElementById("new-pass-input")?.value || "";
    const confirmPass =
        document.getElementById("confirm-new-pass-input")?.value || "";
    const errorAlert = document.getElementById("new-password-alert-error");
    const successAlert = document.getElementById("new-password-alert-success");
    const btn = document.getElementById("save-new-pass-btn");

    if (pass !== confirmPass) {
        if (errorAlert) errorAlert.classList.remove("d-none");
        return;
    }

    if (errorAlert) errorAlert.classList.add("d-none");

    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating Password...';
    }

    setTimeout(() => {
        if (successAlert) successAlert.classList.remove("d-none");
        setTimeout(() => {
            window.location.href = "login.html";
        }, 1200);
    }, 900);
}

/**
 * Simulates OAuth login provider authentication.
 * @param {string} provider - 'GitHub' | 'Google'
 */
function simulateOAuthLogin(provider) {
    alert(`Connecting to ${provider} OAuth 2.0 service...`);
    setTimeout(() => {
        window.location.href = "index.html";
    }, 800);
}

/* ==========================================================================
   HOMEPAGE INTERACTIVITY SYSTEM
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    // Analytics Tab Buttons
    const analyticsTabs = document.querySelectorAll(".analytics-tab-btn");
    if (analyticsTabs.length > 0) {
        analyticsTabs.forEach((btn) => {
            btn.addEventListener("click", () => {
                analyticsTabs.forEach((b) => b.classList.remove("active"));
                btn.classList.add("active");
            });
        });
    }

    // Stat Counter Number Intersection Observer Animation
    const statNumbers = document.querySelectorAll(
        ".stat-counter-number[data-target]",
    );
    if (statNumbers.length > 0 && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const targetEl = entry.target;
                        const targetVal = parseInt(
                            targetEl.getAttribute("data-target"),
                            10,
                        );
                        if (targetVal) {
                            let currentVal = 0;
                            const increment = Math.ceil(targetVal / 25);
                            const timer = setInterval(() => {
                                currentVal += increment;
                                if (currentVal >= targetVal) {
                                    currentVal = targetVal;
                                    clearInterval(timer);
                                }
                                const originalText = targetEl.textContent;
                                const suffix = originalText.includes("+")
                                    ? "+"
                                    : "";
                                const unit = originalText.includes("M")
                                    ? "M"
                                    : originalText.includes("K")
                                      ? "K"
                                      : "";
                                targetEl.textContent =
                                    currentVal + unit + suffix;
                            }, 40);
                        }
                        observer.unobserve(targetEl);
                    }
                });
            },
            { threshold: 0.3 },
        );

        statNumbers.forEach((num) => observer.observe(num));
    }

    // Footer Newsletter Form Submission Handler
    const newsletterForms = document.querySelectorAll(
        ".footer-newsletter-form",
    );
    newsletterForms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const input = form.querySelector("input[type='email']");
            const btn = form.querySelector("button[type='submit']");
            if (input && input.value.trim() && btn) {
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<i class="fa-solid fa-check me-1"></i> Subscribed!';
                btn.classList.remove("btn-subscribe");
                btn.classList.add("btn-success");
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    btn.classList.remove("btn-success");
                    btn.classList.add("btn-subscribe");
                    input.value = "";
                }, 3000);
            }
        });
    });
});

/* ==========================================================================
   5. UNIVERSAL INFINITE SCROLLED LOADING ENGINE
   ========================================================================== */
const UniversalInfiniteScroller = {
    observer: null,
    scrollThrottleTimer: null,

    init() {
        if (!("IntersectionObserver" in window)) return;

        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const loader = entry.target;
                        if (loader.getAttribute("data-loading") === "false") {
                            this.fetchNextPage(loader);
                        }
                    }
                });
            },
            {
                root: null,
                rootMargin: "0px 0px 450px 0px",
                threshold: 0.05,
            },
        );

        this.observeElements(document);

        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (
                            node.classList &&
                            node.classList.contains("universal-infinite-loader")
                        ) {
                            this.observer.observe(node);
                            this.checkAndFetchIfVisible(node);
                        } else if (node.querySelectorAll) {
                            node.querySelectorAll(
                                ".universal-infinite-loader",
                            ).forEach((elem) => {
                                this.observer.observe(elem);
                                this.checkAndFetchIfVisible(elem);
                            });
                        }
                    }
                });
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });

        window.addEventListener(
            "scroll",
            () => {
                if (!this.scrollThrottleTimer) {
                    this.scrollThrottleTimer = setTimeout(() => {
                        this.scrollThrottleTimer = null;
                        document
                            .querySelectorAll(
                                ".universal-infinite-loader[data-loading='false']",
                            )
                            .forEach((loader) => {
                                this.checkAndFetchIfVisible(loader);
                            });
                    }, 180);
                }
            },
            { passive: true },
        );
    },

    observeElements(container) {
        if (!this.observer) return;
        container
            .querySelectorAll(".universal-infinite-loader")
            .forEach((loader) => {
                this.observer.observe(loader);
                this.checkAndFetchIfVisible(loader);
            });
    },

    checkAndFetchIfVisible(loader) {
        if (
            !loader ||
            !document.body.contains(loader) ||
            loader.getAttribute("data-loading") !== "false"
        ) {
            return;
        }
        const rect = loader.getBoundingClientRect();
        const windowHeight =
            window.innerHeight || document.documentElement.clientHeight;
        if (rect.top <= windowHeight + 450 && rect.bottom >= -100) {
            this.fetchNextPage(loader);
        }
    },

    fetchNextPage(loader) {
        const nextUrl = loader.getAttribute("data-next-url");
        if (!nextUrl) return;

        loader.setAttribute("data-loading", "true");

        fetch(nextUrl, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-Infinite-Scroll": "true",
            },
        })
            .then((response) => response.text())
            .then((html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                let targetSelector = loader.getAttribute(
                    "data-target-container",
                );
                let currentContainer = null;
                let newContainer = null;

                if (targetSelector && targetSelector.trim() !== "") {
                    currentContainer = document.querySelector(targetSelector);
                    newContainer = doc.querySelector(targetSelector);
                } else {
                    const parentCard =
                        loader.closest(".card") || loader.parentElement;
                    currentContainer = parentCard
                        ? parentCard.querySelector("tbody") ||
                          parentCard.querySelector(".row") ||
                          parentCard.querySelector("ul")
                        : null;
                    if (currentContainer && currentContainer.id) {
                        newContainer = doc.getElementById(currentContainer.id);
                    } else if (
                        currentContainer &&
                        currentContainer.tagName === "TBODY"
                    ) {
                        const tableId = currentContainer.closest("table")?.id;
                        newContainer = tableId
                            ? doc.querySelector(`#${tableId} tbody`)
                            : doc.querySelector("tbody");
                    }
                }

                if (currentContainer && newContainer) {
                    const fragment = document.createDocumentFragment();
                    Array.from(newContainer.children).forEach((child) => {
                        child.style.opacity = "0";
                        child.style.transition = "opacity 0.35s ease-in";
                        fragment.appendChild(child);
                    });

                    currentContainer.appendChild(fragment);

                    requestAnimationFrame(() => {
                        Array.from(currentContainer.children).forEach(
                            (child) => {
                                if (child.style.opacity === "0") {
                                    child.style.opacity = "1";
                                }
                            },
                        );
                    });
                }

                const newLoader = doc.querySelector(
                    ".universal-infinite-loader",
                );
                const newEndMessage = doc.querySelector(
                    ".universal-infinite-end",
                );

                if (newLoader && newLoader.getAttribute("data-next-url")) {
                    loader.setAttribute(
                        "data-next-url",
                        newLoader.getAttribute("data-next-url"),
                    );
                    loader.setAttribute("data-loading", "false");

                    if (this.observer) {
                        this.observer.unobserve(loader);
                        this.observer.observe(loader);
                    }
                    setTimeout(() => this.checkAndFetchIfVisible(loader), 120);
                } else if (newEndMessage) {
                    loader.insertAdjacentHTML(
                        "afterend",
                        newEndMessage.outerHTML,
                    );
                    if (this.observer) this.observer.unobserve(loader);
                    loader.remove();
                } else {
                    if (this.observer) this.observer.unobserve(loader);
                    loader.remove();
                }
            })
            .catch((err) => {
                console.error("Infinite scroll fetch failed:", err);
                loader.setAttribute("data-loading", "false");
                setTimeout(() => this.checkAndFetchIfVisible(loader), 1500);
            });
    },
};

document.addEventListener("DOMContentLoaded", () => {
    UniversalInfiniteScroller.init();
});

