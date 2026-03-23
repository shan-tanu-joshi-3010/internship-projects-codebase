<?php
include "db.php";

// Fetch license + software data
$query = "
SELECT software.software_name, software.total_licenses,
       licenses.used_licenses, licenses.expiry_date
FROM software
JOIN licenses ON software.id = licenses.software_id
";

$result = mysqli_query($conn, $query);

$licenses_data = [];

while ($row = mysqli_fetch_assoc($result)) {

    $total = (int)$row['total_licenses'];
    $used = (int)$row['used_licenses'];

    if ($total <= 0) continue;

    $unused_percent = (($total - $used) / $total) * 100;
    $days_to_expiry = (int)((strtotime($row['expiry_date']) - time()) / (60*60*24));
    $avg_usage = max(1, round($used / 30));

    // ✅ FIX 1: Correct Python path (escaped properly)
    $python = "C:\\Users\\nikku\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";

    // ✅ FIX 2: Build safe command
    $command = "\"$python\" ml/explain_risk.py $total $used $unused_percent $days_to_expiry $avg_usage";

    // Execute Python
    $output = shell_exec($command);

    // ✅ FIX 3: Robust output parsing
    $output = trim($output);
    $lines = preg_split("/\r\n|\n|\r/", $output);

    $risk = isset($lines[0]) && !empty($lines[0]) ? trim($lines[0]) : "UNKNOWN";

    $licenses_data[] = [
        'name' => $row['software_name'],
        'risk' => $risk,
        'total' => $total,
        'used' => $used,
        'expiry' => $row['expiry_date'],
        'days_to_expiry' => $days_to_expiry,
        'unused_percent' => $unused_percent,
        'factors' => array_filter(array_map('trim', array_slice($lines, 1)))
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ML Risk Analysis - License Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            color: white;
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .risk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .risk-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #667eea;
            animation: slideIn 0.5s ease-out;
        }

        .risk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .risk-card.critical-risk {
            border-left-color: #f5576c;
        }

        .risk-card.compliance-risk {
            border-left-color: #ffa502;
        }

        .risk-card.wastage-risk {
            border-left-color: #2196f3;
        }

        .risk-card.normal-risk {
            border-left-color: #28a745;
        }

        .software-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .risk-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .risk-high {
            background-color: #ffe5e8;
            color: #f5576c;
        }

        .risk-compliance {
            background-color: #fff4e5;
            color: #ffa502;
        }

        .risk-wastage {
            background-color: #e3f2fd;
            color: #2196f3;
        }

        .risk-normal {
            background-color: #e8f5e9;
            color: #28a745;
        }

        .risk-critical {
            background-color: #ffe5e8;
            color: #f5576c;
        }

        .risk-unknown {
            background-color: #f0f0f0;
            color: #666;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            padding: 12px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .stat-item-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-item-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .factors-section {
            margin-top: 20px;
        }

        .factors-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .factors-list {
            list-style: none;
            padding: 0;
        }

        .factors-list li {
            padding: 8px 12px;
            background: #f9f9f9;
            border-left: 3px solid #667eea;
            margin-bottom: 8px;
            border-radius: 3px;
            font-size: 13px;
            color: #555;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            color: #666;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 24px;
            }

            .risk-grid {
                grid-template-columns: 1fr;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .header-nav {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <h1 class="page-title">🤖 ML Risk Analysis</h1>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
        </div>

        <?php if (empty($licenses_data)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No License Data Available</h3>
                <p>Add software and licenses to see risk analysis</p>
            </div>
        <?php else: ?>
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($licenses_data); ?></div>
                    <div class="stat-label">Total Licenses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($licenses_data, fn($l) => strpos($l['risk'], 'CRITICAL') !== false)); ?></div>
                    <div class="stat-label">Critical</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($licenses_data, fn($l) => strpos($l['risk'], 'COMPLIANCE') !== false)); ?></div>
                    <div class="stat-label">Compliance Risk</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($licenses_data, fn($l) => strpos($l['risk'], 'WASTAGE') !== false)); ?></div>
                    <div class="stat-label">Wastage Risk</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count(array_filter($licenses_data, fn($l) => strpos($l['risk'], 'NORMAL') !== false)); ?></div>
                    <div class="stat-label">Normal</div>
                </div>
            </div>

            <div class="risk-grid">
                <?php foreach ($licenses_data as $license): 
                    $risk_class = match(true) {
                        strpos($license['risk'], 'CRITICAL') !== false => 'critical-risk',
                        strpos($license['risk'], 'COMPLIANCE') !== false => 'compliance-risk',
                        strpos($license['risk'], 'WASTAGE') !== false => 'wastage-risk',
                        strpos($license['risk'], 'NORMAL') !== false => 'normal-risk',
                        default => 'unknown-risk'
                    };
                    $badge_class = match(true) {
                        strpos($license['risk'], 'CRITICAL') !== false => 'risk-critical',
                        strpos($license['risk'], 'COMPLIANCE') !== false => 'risk-compliance',
                        strpos($license['risk'], 'WASTAGE') !== false => 'risk-wastage',
                        strpos($license['risk'], 'NORMAL') !== false => 'risk-normal',
                        default => 'risk-unknown'
                    };
                ?>
                    <div class="risk-card <?php echo $risk_class; ?>">
                        <div class="software-name">
                            💾 <?php echo htmlspecialchars($license['name']); ?>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <span class="risk-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($license['risk']); ?>
                            </span>
                        </div>

                        <div class="stats-section">
                            <div class="stat-item">
                                <div class="stat-item-label">Total Licenses</div>
                                <div class="stat-item-value"><?php echo $license['total']; ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-item-label">Used</div>
                                <div class="stat-item-value"><?php echo $license['used']; ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-item-label">Unused %</div>
                                <div class="stat-item-value"><?php echo round($license['unused_percent'], 1); ?>%</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-item-label">Days to Expiry</div>
                                <div class="stat-item-value"><?php echo $license['days_to_expiry']; ?></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div class="stat-item-label">Usage Rate</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo round(($license['used'] / $license['total']) * 100); ?>%"></div>
                            </div>
                        </div>

                        <?php if (!empty($license['factors'])): ?>
                            <div class="factors-section">
                                <div class="factors-title">📊 Risk Factors</div>
                                <ul class="factors-list">
                                    <?php foreach ($license['factors'] as $factor): ?>
                                        <li><?php echo htmlspecialchars($factor); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
