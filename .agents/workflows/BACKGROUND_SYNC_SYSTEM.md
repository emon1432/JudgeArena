# Background Sync System Audit

## Overview
The `judgearena:sync` command (`app/Console/Commands/SyncCommand.php`) is the core mechanism responsible for synchronizing data (Contests, Problems, Users, Submissions, etc.) between external platforms (like Codeforces) and our local database. It operates on a job-based architecture, driven by the `platform_sync_jobs` database table.

## The `platform_sync_jobs` Table
This table acts as the registry for all scheduled synchronization tasks. 
- **`platform_id`**: Foreign key linking to the `platforms` table (e.g., Codeforces = 1).
- **`entity`**: The type of data to sync (`contest`, `problem`, `user`, `submission`, etc.).
- **`enabled`**: Boolean flag to turn a specific sync job on or off.
- **`priority`**: Higher priority tasks (e.g. 100) are executed before lower priority ones (e.g. 10).
- **`interval_minutes`**: The cooldown/wait time between two consecutive successful runs.
- **State tracking**: Columns like `last_started_at`, `last_finished_at`, `last_success_at`, `last_failed_at`, `last_error`, and `metadata` track the exact lifecycle and history of the job.

## Execution Flow (`SyncCommand.php`)

1. **Initialization:**
   The command is triggered (likely via Laravel's Task Scheduler running it every minute). It injects `ApplicationLogger`, `SyncSchedulerService`, and `SyncRunnerService` through the constructor.

2. **Fetching Due Jobs (`SyncSchedulerService`):**
   - The command calls `$this->scheduler->getDueJobs()`.
   - The scheduler queries `PlatformSyncJob` where `enabled = true`.
   - It orders the results descending by `priority`, meaning critical entities like Contests/Problems can be processed before historical submissions.
   - It iterates through them and calls `$job->isDue()`. This model method likely checks if `now() >= last_finished_at + interval_minutes` or if the job has never run before.

3. **Execution (`SyncRunnerService`):**
   - If no jobs are due, it logs "No synchronization jobs are due" and exits.
   - If jobs exist, it initializes a console progress bar.
   - It loops through each due job and passes it to `$this->runner->run($job)`.
   - The runner resolves the appropriate importer for the `platform` and `entity`, executes the import, and updates the state tracking columns in `platform_sync_jobs`.

4. **Result Processing & Logging:**
   - The runner returns an enum status: `SyncRunStatus::Success`, `Skipped`, or `Failed`.
   - The command increments its internal counters based on this status and advances the progress bar.
   - Finally, a summary table is printed to the console, and detailed metrics are logged using the `ApplicationLogger`.

## Seeders (Data Initialization)
The `PlatformSyncJobsTableSeeder` initializes this system. Currently, it is configured exclusively for **Codeforces** (`platform_id = 1`), with appropriate interval pacing to avoid rate limits:
- **`contest`**: priority 100, interval 15 mins
- **`problem`**: priority 90, interval 15 mins
- **`user`**: priority 80, interval 30 mins
- **`submission`**: priority 70, interval 5 mins (high frequency for live status)
- **`standing`**: priority 60, interval 15 mins
- **`rating_change`**: priority 50, interval 60 mins
- **`user_rating_history`**: priority 40, interval 120 mins
- **`user_submissions`**: priority 30, interval 30 mins
