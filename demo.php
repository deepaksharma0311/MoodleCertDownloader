<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Certificate Download Plugin - Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #0073aa;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .btn {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #005a87;
        }
        .stats {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .module-list {
            list-style-type: none;
            padding: 0;
        }
        .module-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .module-list li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
        }
        .file-structure {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            font-family: monospace;
            margin: 20px 0;
        }
        .feature-list {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>IOMAD Bulk Certificate Download Plugin</h1>
        
        <div class="info-box">
            <h3>Plugin Overview</h3>
            <p>This IOMAD-based Moodle local plugin allows company managers to download certificates for their company users as a single ZIP archive. The plugin integrates with IOMAD's multi-tenancy system to provide company-specific certificate management.</p>
        </div>

        <div class="stats">
            <h3>Company Selection & Certificate Statistics</h3>
            <div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 4px;">
                <label for="company-select"><strong>Select Company:</strong></label>
                <select id="company-select" style="margin-left: 10px; padding: 5px;">
                    <option value="0">All Companies</option>
                    <option value="1">Acme Corporation (89 certificates)</option>
                    <option value="2" selected>TechFlow Solutions (76 certificates)</option>
                    <option value="3">Global Learning Inc (82 certificates)</option>
                </select>
            </div>
            <p><strong>Selected Company:</strong> TechFlow Solutions</p>
            <p><strong>Company Users:</strong> 45 active users</p>
            <p><strong>Certificates Found for Company:</strong> 76 certificates</p>
            <p><strong>Certificate Modules Detected:</strong></p>
            <ul class="module-list">
                <li>Certificate (mod_certificate) - 42 certificates</li>
                <li>Custom Certificate (mod_customcert) - 34 certificates</li>
            </ul>
        </div>

        <div class="feature-list">
            <h3>IOMAD Integration Features</h3>
            <ul>
                <li><strong>Company-Based Filtering:</strong> Download certificates only for selected company users</li>
                <li><strong>Multi-Tenancy Support:</strong> Full integration with IOMAD's company structure</li>
                <li><strong>Role-Based Access:</strong> Company managers can access only their company data</li>
                <li><strong>Bulk Download:</strong> Download all company certificates in one ZIP file</li>
                <li><strong>Organized Structure:</strong> Files organized by company, module type, and user name</li>
                <li><strong>Multiple Formats:</strong> Supports both mod_certificate and mod_customcert</li>
                <li><strong>Security:</strong> Proper capability checks with company-level permissions</li>
                <li><strong>Error Handling:</strong> Comprehensive error handling and user feedback</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="#" class="btn" onclick="simulateDownload()">Download All Certificates</a>
        </div>

        <div class="file-structure">
            <h4>ZIP Archive Structure Example:</h4>
            <pre>certificates_2025-06-23_12-08-45.zip
├── certificate/
│   ├── Smith_John/
│   │   └── Smith_John_Course_Completion_ABC123.pdf
│   └── Doe_Jane/
│       └── Doe_Jane_Advanced_Training_DEF456.pdf
└── customcert/
    ├── Wilson_Mary/
    │   └── Wilson_Mary_Professional_Cert_GHI789.pdf
    └── Brown_David/
        └── Brown_David_Skills_Assessment_JKL012.pdf</pre>
        </div>

        <div class="info-box">
            <h3>Installation Instructions</h3>
            <ol>
                <li>Place the plugin files in <code>/local/bulkcertdownload/</code></li>
                <li>Visit Site Administration → Notifications to complete installation</li>
                <li>Access the plugin at Site Administration → Users → Bulk Certificate Download</li>
                <li>Ensure you have the capability <code>local/bulkcertdownload:download</code></li>
            </ol>
        </div>

        <div class="stats">
            <h3>Plugin Files Structure</h3>
            <div class="file-structure">
                <pre>local/bulkcertdownload/
├── version.php          (Plugin version and metadata)
├── db/
│   └── access.php       (Capability definitions)
├── lang/
│   └── en/
│       └── local_bulkcertdownload.php (Language strings)
├── index.php            (Main interface page)
├── download.php         (Download handler)
├── lib.php              (Core library functions)
└── README.md            (Documentation)</pre>
            </div>
        </div>
    </div>

    <script>
        function simulateDownload() {
            alert('In a real Moodle environment, this would start downloading a ZIP file containing all user certificates organized by module type and user name.');
        }
    </script>
</body>
</html>