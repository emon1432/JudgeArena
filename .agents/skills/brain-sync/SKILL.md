---
name: brain-sync
description: Cross-platform export and import utility for Antigravity IDE chat history and brain memory state across Windows and Ubuntu/Linux PCs.
---

# Antigravity Brain Sync Utility

This skill provides automated cross-platform scripts to package and transfer active conversation history and brain transcripts between Windows and Linux (Ubuntu 24.04) environments.

## Directory Paths
- **Windows**: `%USERPROFILE%\.gemini\antigravity-ide\brain\`
- **Linux (Ubuntu 24.04)**: `~/.gemini/antigravity-ide/brain/`

## Usage Commands

### On Windows (PowerShell)

```powershell
# Export conversation history to ZIP package
.\.agents\scripts\sync-brain.ps1 -Action export -ConversationId "560ac77e-8b00-47fa-b3d4-ae050b620881" -ArchivePath "brain-backup.zip"

# Import conversation history from ZIP package
.\.agents\scripts\sync-brain.ps1 -Action import -ArchivePath "brain-backup.zip"
```

### On Linux (Ubuntu 24.04 / Bash)

```bash
# Make script executable
chmod +x ./.agents/scripts/sync-brain.sh

# Export conversation history to ZIP package
./.agents/scripts/sync-brain.sh export 560ac77e-8b00-47fa-b3d4-ae050b620881 brain-backup.zip

# Import conversation history from ZIP package
./.agents/scripts/sync-brain.sh import 560ac77e-8b00-47fa-b3d4-ae050b620881 brain-backup.zip
```
