<?php
/**
 * TechText - Server Requirements Check
 * Run this file to verify your server meets all requirements
 */

$checks = [
    'PHP Version' => [
        'required' => '7.4.0',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'SQLite3 Extension' => [
        'required' => 'Enabled',
        'current' => extension_loaded('sqlite3') ? 'Enabled' : 'Disabled',
        'status' => extension_loaded('sqlite3')
    ],
    'PDO SQLite' => [
        'required' => 'Enabled',
        'current' => in_array('sqlite', PDO::getAvailableDrivers()) ? 'Enabled' : 'Disabled',
        'status' => in_array('sqlite', PDO::getAvailableDrivers())
    ],
    'Data Directory Writable' => [
        'required' => 'Yes',
        'current' => is_writable(__DIR__ . '/data') || !is_dir(__DIR__ . '/data') ? 'Yes' : 'No',
        'status' => is_writable(__DIR__ . '/data') || !is_dir(__DIR__ . '/data')
    ],
    'Fileinfo Extension' => [
        'required' => 'Enabled',
        'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Disabled',
        'status' => extension_loaded('fileinfo')
    ],
    'JSON Extension' => [
        'required' => 'Enabled',
        'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
        'status' => extension_loaded('json')
    ]
];

$allPassed = true;
foreach ($checks as $check) {
    if (!$check['status']) {
        $allPassed = false;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechText - Server Requirements Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }
        .status-banner {
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .status-banner.success {
            background: #d1fae5;
            color: #065f46;
        }
        .status-banner.error {
            background: #fee2e2;
            color: #991b1b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .status-pass {
            color: #059669;
            font-weight: 600;
        }
        .status-fail {
            color: #dc2626;
            font-weight: 600;
        }
        .icon {
            display: inline-block;
            width: 20px;
            margin-right: 8px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .next-steps {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        .next-steps h3 {
            color: #1e40af;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 15px;
        }
        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TechText - Server Requirements Check</h1>
        <p class="subtitle">Checking if your server meets all requirements for TechText</p>
        
        <?php if ($allPassed): ?>
        <div class="status-banner success">
            <span class="icon">✓</span> All requirements met! You're ready to install TechText.
        </div>
        <?php else: ?>
        <div class="status-banner error">
            <span class="icon">✗</span> Some requirements are not met. Please fix the issues below.
        </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Required</th>
                    <th>Current</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $name => $check): ?>
                <tr>
                    <td><?php echo htmlspecialchars($name); ?></td>
                    <td><?php echo htmlspecialchars($check['required']); ?></td>
                    <td><?php echo htmlspecialchars($check['current']); ?></td>
                    <td class="<?php echo $check['status'] ? 'status-pass' : 'status-fail'; ?>">
                        <?php echo $check['status'] ? '✓ PASS' : '✗ FAIL'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($allPassed): ?>
        <div class="next-steps">
            <h3>🎉 Installation Complete!</h3>
            <p>Your server meets all requirements. The application is ready to use.</p>
            <a href="index.php" class="btn">Launch TechText</a>
        </div>
        <?php else: ?>
        <div class="next-steps">
            <h3>🔧 How to Fix Issues</h3>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>PHP Version:</strong> Upgrade to PHP 7.4 or higher</li>
                <li><strong>SQLite3 Extension:</strong> Install php-sqlite3 package</li>
                <li><strong>Data Directory:</strong> Create data/ directory and make it writable (chmod 755)</li>
                <li><strong>Fileinfo Extension:</strong> Install php-fileinfo package</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><strong>TechText</strong> - Built by Santosh Baral | 
            <a href="https://techzeninc.com" target="_blank">Techzen Corporation</a></p>
        </div>
    </div>
</body>
</html>