# Bulk Certificate Download Plugin for Moodle

This Moodle local plugin allows administrators to download all user certificates as a single ZIP archive.

## Features

- Download all certificates from supported certificate modules
- Creates organized ZIP archive with folder structure
- Supports both mod_certificate and mod_customcert modules
- Admin-only access with proper capability checks
- Error handling and user feedback
- Clean filename generation for cross-platform compatibility

## Requirements

- Moodle 3.9 or later
- PHP ZipArchive extension
- At least one certificate module installed (mod_certificate or mod_customcert)
- Administrator privileges

## Installation

1. Download or clone this plugin to your Moodle installation:
   ```
   /path/to/moodle/local/bulkcertdownload/
   ```

2. Visit your Moodle admin notifications page to complete the installation:
   ```
   Site administration → Notifications
   