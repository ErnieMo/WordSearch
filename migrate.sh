#!/bin/bash

# Database Migration Script
# This script backs up wordsearch_dev database (from .env) and restores it to nofinway
# 
# Prerequisites:
# - PostgreSQL client tools (psql, pg_dump, pg_restore) must be installed
# - User must have appropriate permissions to create databases and restore data
# - Source database (wordsearch_dev) must exist and be accessible
# - .env file must exist with database configuration

set -e  # Exit on any error

# Configuration - Will be loaded from .env file
ENV_FILE=".env"
SOURCE_DB="wordsearch_dev"  # Default, will be overridden by .env
TARGET_DB="nofinway_dev"
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${SOURCE_DB}_${TIMESTAMP}.sql"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to load environment variables from .env file
load_env() {
    if [ ! -f "$ENV_FILE" ]; then
        print_error "Environment file '$ENV_FILE' not found"
        exit 1
    fi
    
    print_status "Loading database configuration from '$ENV_FILE'..."
    
    # Source the .env file to load variables
    set -a  # automatically export all variables
    source "$ENV_FILE"
    set +a  # stop automatically exporting
    
    # Set database connection variables
    DB_HOST="${DB_HOST:-localhost}"
    DB_PORT="${DB_PORT:-5432}"
    DB_DATABASE="${DB_DATABASE:-wordsearch_dev}"
    DB_USERNAME="${DB_USERNAME:-wordsearch_dev_user}"
    DB_PASSWORD="${DB_PASSWORD:-}"
    
    # Update SOURCE_DB to use the value from .env
    SOURCE_DB="$DB_DATABASE"
    BACKUP_FILE="${BACKUP_DIR}/${SOURCE_DB}_${TIMESTAMP}.sql"
    
    print_status "Database configuration loaded:"
    print_status "  Host: $DB_HOST"
    print_status "  Port: $DB_PORT"
    print_status "  Database: $DB_DATABASE"
    print_status "  Username: $DB_USERNAME"
    
    if [ -z "$DB_PASSWORD" ]; then
        print_warning "No database password found in .env file"
    fi
}

# Function to check if PostgreSQL command exists
check_command() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 is not installed or not in PATH"
        exit 1
    fi
}

# Function to check database connection
check_db_connection() {
    local db_name=$1
    
    # Set PGPASSWORD environment variable for this command only
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    if ! psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -lqt | cut -d \| -f 1 | grep -qw "$db_name"; then
        print_error "Database '$db_name' does not exist or is not accessible"
        print_error "Connection details: $DB_USERNAME@$DB_HOST:$DB_PORT"
        return 1
    fi
    
    # Clear password from environment
    unset PGPASSWORD
    
    return 0
}

# Function to check if target database exists
check_target_exists() {
    # Set PGPASSWORD environment variable for this command only
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    if psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -lqt | cut -d \| -f 1 | grep -qw "$TARGET_DB"; then
        print_warning "Database '$TARGET_DB' already exists"
        read -p "Do you want to drop it and continue? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            print_status "Dropping existing database '$TARGET_DB'..."
            dropdb -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" "$TARGET_DB" || {
                print_error "Failed to drop database '$TARGET_DB'"
                exit 1
            }
            print_success "Database '$TARGET_DB' dropped successfully"
        else
            print_status "Migration cancelled by user"
            exit 0
        fi
    fi
    
    # Clear password from environment
    unset PGPASSWORD
}

# Function to check database creation permissions
check_create_permissions() {
    print_status "Checking database creation permissions..."
    
    # Set PGPASSWORD environment variable for this command only
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    # Check if user can create databases
    CAN_CREATE=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -t -c "SELECT rolcreatedb FROM pg_roles WHERE rolname = current_user;" | xargs)
    
    # Clear password from environment
    unset PGPASSWORD
    
    if [ "$CAN_CREATE" = "t" ]; then
        print_success "User has permission to create databases"
        return 0
    else
        print_warning "User '$DB_USERNAME' does not have permission to create databases"
        print_status "Alternative solutions:"
        print_status "1. Ask a PostgreSQL superuser to create the 'nofinway' database for you"
        print_status "2. Use a different user with CREATE DATABASE privileges"
        print_status "3. Manually create the database using: sudo -u postgres createdb nofinway"
        return 1
    fi
}

# Function to create database with alternative methods
create_database_alternative() {
    print_status "Attempting alternative database creation methods..."
    
    # Method 1: Try using postgres user if available
    if command -v sudo &> /dev/null; then
        print_status "Trying to create database using postgres system user..."
        if sudo -u postgres createdb "$TARGET_DB" 2>/dev/null; then
            print_success "Database '$TARGET_DB' created successfully using postgres user"
            return 0
        fi
    fi
    
    # Method 2: Try using psql with postgres user
    if command -v sudo &> /dev/null; then
        print_status "Trying to create database using psql with postgres user..."
        if sudo -u postgres psql -c "CREATE DATABASE \"$TARGET_DB\";" 2>/dev/null; then
            print_success "Database '$TARGET_DB' created successfully using psql"
            return 0
        fi
    fi
    
    # Method 3: Check if database already exists (maybe created by another user)
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    if psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -lqt | cut -d \| -f 1 | grep -qw "$TARGET_DB"; then
        print_success "Database '$TARGET_DB' already exists and is accessible"
        unset PGPASSWORD
        return 0
    fi
    
    unset PGPASSWORD
    return 1
}

# Function to grant database permissions to the application user
grant_database_permissions() {
    print_status "Granting database permissions to '$DB_USERNAME'..."
    
    # Grant database-level privileges
    print_status "Granting database privileges..."
    if ! sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE \"$TARGET_DB\" TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to grant database privileges (this may already be set)"
    else
        print_success "Database privileges granted"
    fi
    
    # Grant schema-level privileges
    print_status "Granting schema privileges..."
    if ! sudo -u postgres psql -d "$TARGET_DB" -c "GRANT ALL ON SCHEMA public TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to grant schema privileges (this may already be set)"
    else
        print_success "Schema privileges granted"
    fi
    
    # Grant table privileges (for existing tables)
    print_status "Granting table privileges..."
    if ! sudo -u postgres psql -d "$TARGET_DB" -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to grant table privileges (this may already be set)"
    else
        print_success "Table privileges granted"
    fi
    
    # Grant sequence privileges (for existing sequences)
    print_status "Granting sequence privileges..."
    if ! sudo -u postgres psql -d "$TARGET_DB" -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to grant sequence privileges (this may already be set)"
    else
        print_success "Sequence privileges granted"
    fi
    
    # Set default privileges for future objects
    print_status "Setting default privileges for future objects..."
    if ! sudo -u postgres psql -d "$TARGET_DB" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to set default table privileges (this may already be set)"
    else
        print_success "Default table privileges set"
    fi
    
    if ! sudo -u postgres psql -d "$TARGET_DB" -c "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO \"$DB_USERNAME\";" 2>/dev/null; then
        print_warning "Failed to set default sequence privileges (this may already be set)"
    else
        print_success "Default sequence privileges set"
    fi
    
    print_success "Database permissions setup completed"
}

# Main execution
main() {
    print_status "Starting database migration from '$SOURCE_DB' to '$TARGET_DB'"
    
    # Load environment configuration
    load_env
    
    # Check prerequisites
    print_status "Checking prerequisites..."
    check_command "psql"
    check_command "pg_dump"
    check_command "createdb"
    check_command "dropdb"
    
    # Create backup directory if it doesn't exist
    if [ ! -d "$BACKUP_DIR" ]; then
        print_status "Creating backup directory: $BACKUP_DIR"
        mkdir -p "$BACKUP_DIR"
    fi
    
    # Check source database exists
    print_status "Checking source database '$SOURCE_DB'..."
    if ! check_db_connection "$SOURCE_DB"; then
        print_error "Source database '$SOURCE_DB' is not accessible"
        print_error "Please check your database configuration and permissions"
        exit 1
    fi
    print_success "Source database '$SOURCE_DB' is accessible"
    
    # Check if target database exists and handle accordingly
    check_target_exists
    
    # Check database creation permissions
    if ! check_create_permissions; then
        print_status "Attempting to create database using alternative methods..."
        if create_database_alternative; then
            print_success "Database '$TARGET_DB' created/accessed successfully using alternative method"
            # Grant database permissions to the application user
            grant_database_permissions
        else
            print_error "Failed to create database '$TARGET_DB' using all available methods"
            print_error "Please create the database manually or contact your database administrator"
            print_status "Manual creation command: sudo -u postgres createdb nofinway_dev"
            exit 1
        fi
    else
        # Create target database using normal method
        print_status "Creating target database '$TARGET_DB'..."
        if [ -n "$DB_PASSWORD" ]; then
            export PGPASSWORD="$DB_PASSWORD"
        fi
        
        createdb -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" "$TARGET_DB" || {
            print_error "Failed to create database '$TARGET_DB'"
            exit 1
        }
        print_success "Target database '$TARGET_DB' created successfully"
    fi
    
    # Grant database permissions to the application user
    grant_database_permissions
    
    # Create backup
    print_status "Creating backup of '$SOURCE_DB'..."
    print_status "Backup file: $BACKUP_FILE"
    
    # Set password for backup operation
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    if pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" "$SOURCE_DB" > "$BACKUP_FILE"; then
        print_success "Backup created successfully"
        print_status "Backup size: $(du -h "$BACKUP_FILE" | cut -f1)"
    else
        print_error "Failed to create backup"
        exit 1
    fi
    
    # Clear password from environment
    unset PGPASSWORD
    
    # Restore backup to target database
    print_status "Restoring backup to '$TARGET_DB'..."
    
    # Set password for restore operation
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    if psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" "$TARGET_DB" < "$BACKUP_FILE"; then
        print_success "Restore completed successfully"
    else
        print_error "Failed to restore backup to '$TARGET_DB'"
        print_error "You may need to manually clean up the target database"
        exit 1
    fi
    
    # Clear password from environment
    unset PGPASSWORD
    
    # Verify migration
    print_status "Verifying migration..."
    
    # Set password for verification operations
    if [ -n "$DB_PASSWORD" ]; then
        export PGPASSWORD="$DB_PASSWORD"
    fi
    
    SOURCE_TABLES=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -t -c "SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public';" "$SOURCE_DB" | xargs)
    TARGET_TABLES=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -t -c "SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public';" "$TARGET_DB" | xargs)
    
    if [ "$SOURCE_TABLES" = "$TARGET_TABLES" ]; then
        print_success "Migration verification successful: $TARGET_TABLES tables migrated"
    else
        print_warning "Migration verification: Source has $SOURCE_TABLES tables, target has $TARGET_TABLES tables"
    fi
    
    # Clear password from environment
    unset PGPASSWORD
    
    print_success "Database migration completed successfully!"
    print_status "Source: $SOURCE_DB"
    print_status "Target: $TARGET_DB"
    print_status "Backup: $BACKUP_FILE"
}

# Run main function
main "$@"
