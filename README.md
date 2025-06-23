# IOMAD Bulk Certificate Download Plugin

This IOMAD-based Moodle local plugin allows company managers to download certificates for their company users as a single ZIP archive. The plugin integrates with IOMAD's multi-tenancy system to provide company-specific certificate management.

## Features

- **Company-Based Filtering**: Download certificates only for selected company users
- **Multi-Tenancy Support**: Full integration with IOMAD's company structure
- **Role-Based Access**: Company managers can access only their company data
- **Bulk Download**: Download all company certificates in one ZIP file
- **Organized Structure**: Files organized by company, module type, and user name
- **Multiple Formats**: Supports both mod_certificate and mod_customcert modules
- **Security**: Proper capability checks with company-level permissions
- **Error Handling**: Comprehensive error handling and user feedback

## Requirements

- Moodle 3.9 or later
- **IOMAD installed and configured**
- PHP ZipArchive extension
- At least one certificate module installed (mod_certificate or mod_customcert)
- Company manager or administrator privileges

## Installation

1. **Prerequisites**: Ensure IOMAD is installed and configured in your Moodle instance
2. Download or clone this plugin to your Moodle installation:
   ```
   /path/to/moodle/local/bulkcertdownload/
   ```
3. Visit your Moodle admin notifications page to complete the installation:
   ```
   Site administration → Notifications
   ```
4. Access via IOMAD Company Dashboard → Reports → Bulk Certificate Download
5. Assign capability `local/bulkcertdownload:downloadcompany` to company managers

## Usage

### For Company Managers
1. Login to your IOMAD company dashboard
2. Navigate to Reports → Bulk Certificate Download
3. Select your company from the dropdown (automatically filtered to your assigned companies)
4. Review the certificate count and user statistics
5. Click "Download Company Certificates" to generate and download the ZIP archive

### For Site Administrators
- Can access all companies and download certificates for any company
- Have full system-wide access to certificate data
- Can manage plugin settings and capabilities

## File Structure

The downloaded ZIP file will contain certificates organized as follows:
```
CompanyName_certificates_YYYY-MM-DD_HH-MM-SS.zip
├── certificate/
│   ├── User_Name/
│   │   └── User_Name_Certificate_Title_Code.pdf
│   └── Another_User/
│       └── Another_User_Certificate_Title_Code.pdf
└── customcert/
    ├── User_Name/
    │   └── User_Name_Custom_Certificate_Code.pdf
    └── Another_User/
        └── Another_User_Custom_Certificate_Code.pdf
```

## Capabilities

- `local/bulkcertdownload:download` - Download all certificates (site admin only)
- `local/bulkcertdownload:downloadcompany` - Download certificates for assigned companies (company managers)
   