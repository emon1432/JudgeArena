#!/usr/bin/env bash

ACTION=$1
CONVERSATION_ID=${2:-"560ac77e-8b00-47fa-b3d4-ae050b620881"}
ARCHIVE_PATH=${3:-"brain-backup.zip"}

BRAIN_DIR="$HOME/.gemini/antigravity-ide/brain"

if [ "$ACTION" == "export" ]; then
    TARGET_DIR="$BRAIN_DIR/$CONVERSATION_ID"
    if [ -d "$TARGET_DIR" ]; then
        rm -f "$ARCHIVE_PATH"
        zip -r "$ARCHIVE_PATH" "$CONVERSATION_ID" -x "*.log"
        echo "[SUCCESS] Exported conversation '$CONVERSATION_ID' to '$ARCHIVE_PATH'"
    else
        echo "[ERROR] Conversation directory not found: $TARGET_DIR"
        exit 1
    fi
elif [ "$ACTION" == "import" ]; then
    if [ -f "$ARCHIVE_PATH" ]; then
        mkdir -p "$BRAIN_DIR"
        unzip -o "$ARCHIVE_PATH" -d "$BRAIN_DIR"
        echo "[SUCCESS] Imported conversation into '$BRAIN_DIR'"
    else
        echo "[ERROR] Archive file not found: $ARCHIVE_PATH"
        exit 1
    fi
else
    echo "Usage: ./sync-brain.sh [export|import] [conversation-id] [archive-path]"
    exit 1
fi
