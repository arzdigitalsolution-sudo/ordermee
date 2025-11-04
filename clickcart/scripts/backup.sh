#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="/var/backups/clickcart"
mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
DB_FILE="$BACKUP_DIR/db_${TIMESTAMP}.sql.gz"
FILES_ARCHIVE="$BACKUP_DIR/uploads_${TIMESTAMP}.tar.gz"

mysqldump -u "${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" clickcart | gzip > "$DB_FILE"

tar -czf "$FILES_ARCHIVE" -C /var/www/clickcart/public uploads

find "$BACKUP_DIR" -type f -mtime +14 -delete

echo "Backup stored at $BACKUP_DIR"
