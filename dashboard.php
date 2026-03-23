<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - License Tracker</title>
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            color: #333;
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .nav-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border-left: 5px solid #667eea;
        }

        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .nav-card.add-software {
            border-left-color: #667eea;
        }

        .nav-card.add-license {
            border-left-color: #f093fb;
        }

        .nav-card.view-licenses {
            border-left-color: #4facfe;
        }

        .nav-card.risk-analysis {
            border-left-color: #43e97b;
        }

        .nav-card-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .nav-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .nav-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .nav-card:hover h3 {
            color: #667eea;
        }

        .stats-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 24px;
            }

            .nav-grid {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>📊 License Management Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="nav-grid">
            <a href="add_software.php" class="nav-card add-software">
                <div class="nav-card-icon">💾</div>
                <h3>Add Software</h3>
                <p>Register new software to your inventory</p>
            </a>

            <a href="add_license.php" class="nav-card add-license">
                <div class="nav-card-icon">📝</div>
                <h3>Add License Details</h3>
                <p>Register and manage license information</p>
            </a>

            <a href="view_licenses.php" class="nav-card view-licenses">
                <div class="nav-card-icon">👁️</div>
                <h3>View Licenses</h3>
                <p>View and manage all your licenses</p>
            </a>

            <a href="risk_analysis.php" class="nav-card risk-analysis">
                <div class="nav-card-icon">🤖</div>
                <h3>ML Risk & XAI Analysis</h3>
                <p>AI-powered risk analysis and insights</p>
            </a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number">4</div>
                <div class="stat-label">Main Features</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Secure</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">AI</div>
                <div class="stat-label">Powered</div>
            </div>
        </div>
    </div>
</body>
</html>
