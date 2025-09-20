#!/bin/bash

# Sync documentation changes to main cakephp-docs repository using rsync
# This script syncs ./docs to ../cakephp-docs/docs

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$SCRIPT_DIR/docs/"
TARGET_DIR="$SCRIPT_DIR/../cakephp-docs/docs/"

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

# Parse command line arguments
DRY_RUN=false
VERBOSE=false
DELETE=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -n|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -d|--delete)
            DELETE=true
            shift
            ;;
        -h|--help)
            echo "Usage: $0 [OPTIONS]"
            echo "Sync documentation from ./docs to ../cakephp-docs/docs using rsync"
            echo ""
            echo "Options:"
            echo "  -n, --dry-run    Show what would be synced without making changes"
            echo "  -v, --verbose    Show detailed output"
            echo "  -d, --delete     Delete files in target that don't exist in source"
            echo "  -h, --help       Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0               # Sync all changes"
            echo "  $0 -n            # Preview what would be synced"
            echo "  $0 -v -d         # Verbose sync with deletions"
            exit 0
            ;;
        *)
            print_error "Unknown option: $1"
            echo "Use -h or --help for usage information"
            exit 1
            ;;
    esac
done

# Check if rsync is available
if ! command -v rsync &> /dev/null; then
    print_error "rsync is required but not installed"
    exit 1
fi

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    print_error "Source directory does not exist: $SOURCE_DIR"
    exit 1
fi

# Check if target directory exists
if [ ! -d "$TARGET_DIR" ]; then
    print_error "Target directory does not exist: $TARGET_DIR"
    print_error "Make sure ../cakephp-docs exists and has a docs/ subdirectory"
    exit 1
fi

# Build rsync options
RSYNC_OPTS="-a --human-readable"

if [ "$DRY_RUN" = true ]; then
    RSYNC_OPTS="$RSYNC_OPTS --dry-run"
    print_status "DRY RUN MODE - No files will be modified"
fi

if [ "$VERBOSE" = true ]; then
    RSYNC_OPTS="$RSYNC_OPTS --verbose --progress"
fi

if [ "$DELETE" = true ]; then
    RSYNC_OPTS="$RSYNC_OPTS --delete"
    print_warning "DELETE mode enabled - files in target not present in source will be removed"
fi

# Always exclude some common files
RSYNC_OPTS="$RSYNC_OPTS --exclude=.git --exclude=.DS_Store --exclude=Thumbs.db"

print_status "Syncing documentation..."
print_status "Source: $SOURCE_DIR"
print_status "Target: $TARGET_DIR"

if [ "$DRY_RUN" = true ]; then
    print_status "Preview of changes:"
    echo
fi

# Run rsync
if rsync $RSYNC_OPTS "$SOURCE_DIR" "$TARGET_DIR"; then
    if [ "$DRY_RUN" = true ]; then
        echo
        print_success "Dry run completed successfully"
        print_status "Run without -n/--dry-run to apply these changes"
    else
        echo
        print_success "Documentation synced successfully!"

        # Check if we're in a git repository and show git status
        if git rev-parse --git-dir > /dev/null 2>&1; then
            echo
            print_status "Next steps:"
            print_status "  1. cd ../cakephp-docs"
            print_status "  2. git status  # Review changes"
            print_status "  3. git add docs/"
            print_status "  4. git commit -m \"Update documentation\""
            print_status "  5. git push"
        fi
    fi
else
    print_error "rsync failed"
    exit 1
fi