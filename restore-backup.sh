#!/usr/bin/env bash
# ================================================================
#    CLASSY-ONE ACADEMY - EMERGENCY BACKUP RESTORATION SCRIPT
# ================================================================
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "================================================================"
echo "   CLASSY-ONE ACADEMY - EMERGENCY BACKUP RESTORATION SCRIPT"
echo "================================================================"
echo ""

if ! command -v php &> /dev/null; then
    echo "[CRITICAL ERROR] php command could not be found in PATH."
    exit 1
fi

if [ -z "$1" ]; then
    echo "Options:"
    echo " [1] Restore LATEST backup (Database + Uploaded Files)"
    echo " [2] Restore LATEST backup (Database ONLY)"
    echo " [3] Restore LATEST backup (Files ONLY)"
    echo " [4] Specify a custom backup zip file"
    echo " [5] Exit"
    echo ""
    read -p "Enter choice [1-5]: " CHOICE

    case "$CHOICE" in
        1)
            php artisan backup:restore --latest
            ;;
        2)
            php artisan backup:restore --latest --db-only
            ;;
        3)
            php artisan backup:restore --latest --files-only
            ;;
        4)
            read -p "Enter full path or filename of backup zip: " CUSTOM_FILE
            php artisan backup:restore --file="$CUSTOM_FILE"
            ;;
        *)
            echo "Operation cancelled."
            exit 0
            ;;
    esac
else
    php artisan backup:restore "$@"
fi

echo ""
echo "================================================================"
echo "   Restoration process finished."
echo "================================================================"
