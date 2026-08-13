param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("export", "import")]
    [string]$Action,

    [string]$ConversationId = "560ac77e-8b00-47fa-b3d4-ae050b620881",
    [string]$ArchivePath = "brain-backup.zip"
)

$WinBrainDir = "$env:USERPROFILE\.gemini\antigravity-ide\brain"

if ($Action -eq "export") {
    $TargetDir = "$WinBrainDir\$ConversationId"
    if (Test-Path $TargetDir) {
        if (Test-Path $ArchivePath) { Remove-Item -Force $ArchivePath }
        Compress-Archive -Path $TargetDir -DestinationPath $ArchivePath -Force
        Write-Host "[SUCCESS] Exported conversation '$ConversationId' to '$ArchivePath'" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Conversation directory not found: $TargetDir" -ForegroundColor Red
    }
} elseif ($Action -eq "import") {
    if (Test-Path $ArchivePath) {
        New-Item -ItemType Directory -Force -Path $WinBrainDir | Out-Null
        Expand-Archive -Path $ArchivePath -DestinationPath $WinBrainDir -Force
        Write-Host "[SUCCESS] Imported conversation into '$WinBrainDir'" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Archive file not found: $ArchivePath" -ForegroundColor Red
    }
}
