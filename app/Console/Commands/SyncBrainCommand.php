<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class SyncBrainCommand extends Command
{
    protected $signature = 'judgearena:sync-brain {action : export or import}';

    protected $description = 'Export or import this project conversation history package across Windows and Linux OS.';

    private const DEFAULT_CONVERSATION_ID = '560ac77e-8b00-47fa-b3d4-ae050b620881';
    private const ARCHIVE_FILE = 'brain-backup.zip';

    public function handle(): int
    {
        $action = strtolower(trim((string) $this->argument('action')));

        if (! in_array($action, ['export', 'import'], true)) {
            $this->error('Invalid action. Use "export" or "import".');
            return self::FAILURE;
        }

        $isWindows = PHP_OS_FAMILY === 'Windows';
        $userHome = $isWindows ? getenv('USERPROFILE') : getenv('HOME');

        if (! $userHome) {
            $this->error('Could not determine user home directory.');
            return self::FAILURE;
        }

        $brainDir = $isWindows
            ? rtrim($userHome, '\\/') . '\\.gemini\\antigravity-ide\\brain'
            : rtrim($userHome, '\\/') . '/.gemini/antigravity-ide/brain';

        $conversationId = self::DEFAULT_CONVERSATION_ID;
        $targetDir = $brainDir . ($isWindows ? '\\' : '/') . $conversationId;
        $archiveFullPath = base_path(self::ARCHIVE_FILE);

        try {
            if ($action === 'export') {
                if (! is_dir($targetDir)) {
                    $this->error("Conversation directory not found: {$targetDir}");
                    return self::FAILURE;
                }

                $zip = new \ZipArchive();
                if ($zip->open($archiveFullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                    $this->error("Failed to create archive file: {$archiveFullPath}");
                    return self::FAILURE;
                }

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($targetDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    if (! $file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = $conversationId . '/' . substr($filePath, strlen($targetDir) + 1);
                        $relativePath = str_replace('\\', '/', $relativePath);
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();

                $this->info("[SUCCESS] Exported conversation '{$conversationId}' to '" . self::ARCHIVE_FILE . "'");
                $this->line("Path: {$archiveFullPath}");
                return self::SUCCESS;
            }

            if ($action === 'import') {
                if (! file_exists($archiveFullPath)) {
                    $this->error("Archive file not found: {$archiveFullPath}");
                    return self::FAILURE;
                }

                if (! is_dir($brainDir)) {
                    mkdir($brainDir, 0755, true);
                }

                $zip = new \ZipArchive();
                if ($zip->open($archiveFullPath) !== true) {
                    $this->error("Failed to open archive file: {$archiveFullPath}");
                    return self::FAILURE;
                }

                $zip->extractTo($brainDir);
                $zip->close();

                $this->info("[SUCCESS] Imported conversation '{$conversationId}' into '{$brainDir}'");
                return self::SUCCESS;
            }
        } catch (Throwable $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
