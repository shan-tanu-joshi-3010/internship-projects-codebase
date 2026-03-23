<?php
include "db.php";

$success_message = "";
$error_message = "";

if (isset($_POST['save'])) {
    $software_id = $_POST['software_id'];
    $used = $_POST['used_licenses'];
    $expiry = $_POST['expiry_date'];

    $query = "INSERT INTO licenses (software_id, used_licenses, expiry_date)
              VALUES ('$software_id', '$used', '$expiry')";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "✅ License details saved successfully!";
    } else {
        $error_message = "❌ Error saving license details";
    }
}

$result = mysqli_query($conn, "SELECT * FROM software");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add License - License Tracker</title>
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
            max-width: 600px;
            margin: 0 auto;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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

        .page-title {
            color: white;
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f5576c;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input[type="number"],
        .form-group input[type="date"] {
            cursor: pointer;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .save-btn {
            flex: 1;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .save-btn:active {
            transform: translateY(0);
        }

        .reset-btn {
            flex: 1;
            padding: 14px;
            background: #f0f0f0;
            color: #333;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .reset-btn:hover {
            background: #e0e0e0;
            border-color: #ccc;
        }

        .form-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 25px;
            }

            .page-title {
                font-size: 24px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-nav">
            <h1 class="page-title">📝 Add License</h1>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
        </div>

        <div class="form-card">
            <?php if (!empty($success_message)): ?>
                <div class="message success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="message error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="software_id">Select Software</label>
                    <select name="software_id" id="software_id" required>
                        <option value="">-- Choose Software --</option>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>">
                                <?= htmlspecialchars($row['software_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div class="form-hint">Choose the software for this license</div>
                </div>

                <div class="form-group">
                    <label for="used_licenses">Number of Used Licenses</label>
                    <input 
                        type="number" 
                        id="used_licenses"
                        name="used_licenses" 
                        min="1"
                        max="9999"
                        required
                        placeholder="e.g., 50"
                    >
                    <div class="form-hint">Enter the number of licenses currently in use</div>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input 
                        type="date" 
                        id="expiry_date"
                        name="expiry_date" 
                        required
                    >
                    <div class="form-hint">Set the expiration date for this license</div>
                </div>

                <div class="button-group">
                    <button type="submit" name="save" class="save-btn">💾 Save License</button>
                    <button type="reset" class="reset-btn">🔄 Clear</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
