<?php

declare(strict_types=1);

namespace Tests\Unit\Platforms\Codeforces;

use App\Platforms\Codeforces\Mappers\CodeforcesRatingChangeMapper;
use App\Platforms\Codeforces\Transformers\CodeforcesRatingChangeTransformer;
use Tests\TestCase;

class RatingChangeTransformerTest extends TestCase
{
    private function sample(string $fileName): array
    {
        $path = base_path('docs/platforms/codeforces.com/sample-response/' . $fileName);

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_rating_change_transformer_maps_rating_changes(): void
    {
        $ratingChanges = $this->sample('codeforces-contest-rating-changes.json');
        $dtos = CodeforcesRatingChangeMapper::fromNormalizedList($ratingChanges);
        $coreDtos = CodeforcesRatingChangeTransformer::fromApiRatingChanges($dtos, '2225');

        $this->assertNotEmpty($coreDtos);
        $first = $coreDtos[0];

        $this->assertSame('codeforces', $first->platform);
        $this->assertSame('vietbachleonkroos2326', $first->handle);
        $this->assertTrue($first->isRated);
        $this->assertSame(1664, $first->oldRating);
        $this->assertSame(2060, $first->newRating);
        $this->assertSame(396, $first->ratingChange);
    }
}
