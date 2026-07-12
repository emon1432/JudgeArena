<?php

namespace App\Core\Results;

final class ImportResult
{
    public function __construct(
        public int $checked = 0,
        public int $fetched = 0,
        public int $created = 0,
        public int $updated = 0,
        public int $failed = 0,
        public int $skipped = 0,
        public array $metadata = [],
    ) {}

    public function incrementChecked(int $count = 1): void
    {
        $this->checked += $count;
    }

    public function incrementFetched(int $count = 1): void
    {
        $this->fetched += $count;
    }

    public function incrementCreated(int $count = 1): void
    {
        $this->created += $count;
    }

    public function incrementUpdated(int $count = 1): void
    {
        $this->updated += $count;
    }

    public function incrementFailed(int $count = 1): void
    {
        $this->failed += $count;
    }

    public function incrementSkipped(int $count = 1): void
    {
        $this->skipped += $count;
    }

    public function synced(): int
    {
        return $this->created + $this->updated;
    }

    public function incrementMetadata(string $key, int $by = 1): void
    {
        $this->metadata[$key] = ($this->metadata[$key] ?? 0) + $by;
    }

    public function toArray(): array
    {
        return [
            'checked' => $this->checked,
            'fetched' => $this->fetched,
            'created' => $this->created,
            'updated' => $this->updated,
            'failed' => $this->failed,
            'skipped' => $this->skipped,
            'metadata' => $this->metadata,
        ];
    }
}
