#!/bin/bash

# Marketplace Backup Script
# Run daily via cron: 0 2 * * * /path/to/backup.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/marketplace"
DB_NAME="${DB_DATABASE:-marketplace}"
DB_USER="${DB_USERNAME:-postgres}"
DB_HOST="${DB_HOST:-localhost}"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=7

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo "Starting backup at $(date)"

# 1. Database Backup
echo "Backing up database..."
PGPASSWORD="${DB_PASSWORD}" pg_dump -h "$DB_HOST" -U "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_DIR/db_${TIMESTAMP}.sql.gz"

# 2. Storage Files Backup
echo "Backing up storage files..."
tar -czf "$BACKUP_DIR/storage_${TIMESTAMP}.tar.gz" -C "$(dirname "$0")" storage/app

# 3. Environment Config Backup
echo "Backing up .env file..."
cp .env "$BACKUP_DIR/env_${TIMESTAMP}.backup"

# 4. Remove old backups
echo "Cleaning up old backups..."
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "storage_*.tar.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "env_*.backup" -mtime +$RETENTION_DAYS -delete

# 5. Upload to S3 (optional - uncomment if using AWS)
# aws s3 sync "$BACKUP_DIR" s3://your-backup-bucket/marketplace/ --storage-class STANDARD_IA

echo "Backup completed successfully at $(date)"
echo "Backup location: $BACKUP_DIR"

# Send notification (optional)
# curl -X POST https://your-monitoring-service.com/webhook \
#   -H "Content-Type: application/json" \
#   -d "{\"message\": \"Marketplace backup completed: ${TIMESTAMP}\"}"
