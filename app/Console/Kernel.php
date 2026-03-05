<?php

namespace App\Console;

use App\Console\Commands\TestAtCoderSync;
use App\Console\Commands\TestCodeChefSync;
use App\Console\Commands\TestCodeforcesSync;
use App\Console\Commands\TestHackerEarthSync;
use App\Console\Commands\TestHackerRankSync;
use App\Console\Commands\TestLeetCodeSync;
use App\Console\Commands\TestSpojSync;
use App\Console\Commands\TestTimusSync;
use App\Console\Commands\TestUvaSync;
use App\Console\Commands\SyncCatalogData;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        TestAtCoderSync::class,
        TestCodeChefSync::class,
        TestCodeforcesSync::class,
        TestHackerEarthSync::class,
        TestHackerRankSync::class,
        TestLeetCodeSync::class,
        TestSpojSync::class,
        TestUvaSync::class,
        TestTimusSync::class,
        SyncCatalogData::class,
    ];
}
