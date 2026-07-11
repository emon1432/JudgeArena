<?php

namespace App\Enums;

enum SyncRunStatus: string
{
    case Success = 'success';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
