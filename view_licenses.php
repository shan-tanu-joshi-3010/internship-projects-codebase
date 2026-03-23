<?php
include "db.php";

/* ---------------- PYTHON CONFIG ---------------- */
$python = "C:\\Users\\nikku\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
$ml_script = "C:\\xampp\\htdocs\\license_tracker\\ml\\explain_risk.py";

/* ---------------- FETCH DATA ---------------- */
$query = "
SELECT software.software_name, software.department, software.total_licenses,
       licenses.used_licenses, licenses.expiry_date
FROM software
JOIN licenses ON software.id = licenses.software_id
";

$result = mysqli_query($conn, $query);
$licenses_list = [];

while ($row = mysqli_fetch_assoc($result)) {

    $total = (int)$row['total_licenses'];
    $used  = (int)$row['used_licenses'];
    if ($total <= 0) continue;

    $unused = $total - $used;
    $unused_percent = ($unused / $total) * 100;
    $days_left = (int)((strtotime($row['expiry_date']) - time()) / (60*60*24));

    /* ---------- RULE-BASED RISK (SECONDARY) ---------- */
    if ($days_left <= 30 && $unused_percent >= 50) {
        $rule_risk = "CRITICAL";
    } elseif ($days_left <= 30) {
        $rule_risk = "COMPLIANCE RISK";
    } elseif ($unused_percent >= 50) {
        $rule_risk = "WASTAGE RISK";
    } else {
        $rule_risk = "NORMAL";
    }

    /* ---------- ML-BASED RISK (PRIMARY) ---------- */
    $avg_usage = max(1, round($used / 30));

    $cmd = "\"$python\" \"$ml_script\" "
         . $total . " "
         . $used . " "
         . round($unused_percent, 2) . " "
         . $days_left . " "
         . $avg_usage . " 2>&1";

    $ml_output = shell_exec($cmd);
    $ml_lines = preg_split("/\r\n|\n|\r/", trim($ml_output));
    $ml_risk = $ml_lines[0] ?? "NORMAL";

    switch ($ml_risk) {
        case "CRITICAL":
            $ml_class = "critical";
            break;
        case "COMPLIANCE_RISK":
            $ml_class = "compliance";
            break;
        case "WASTAGE_RISK":
            $ml_class = "wastage";
            break;
        default:
            $ml_class = "normal";
    }

    $licenses_list[] = [
        'software' => $row['software_name'],
        'department' => $row['department'],
        'total' => $total,
        'used' => $used,
        'unused_percent' => $unused_percent,
        'expiry' => $row['expiry_date'],
        'days_left' => $days_left,

        // 🔥 ML = PRIMARY
        'ml_risk' => $ml_risk,
        'ml_class' => $ml_class,

        // Secondary
        'rule_risk' => $rule_risk
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Licenses - License Tracker</title>
<style>
body {
    font-family: Segoe UI, sans-serif;
    background: linear-gradient(135deg,#667eea,#764ba2);
    padding:20px;
}
.container { max-width:1400px; margin:auto; }
.page-title { color:#fff; font-size:30px; margin-bottom:20px; }
.filters-bar { background:#fff;padding:15px;border-radius:10px;margin-bottom:20px; }
.filter-btn {
    padding:8px 14px;border-radius:20px;border:1px solid #ddd;
    background:#fff;cursor:pointer;margin-right:8px;
}
.filter-btn.active { background:#667eea;color:#fff; }
.table-container { background:#fff;border-radius:10px;overflow:hidden; }
.table-header,.table-row {
    display:grid;
    grid-template-columns:2fr 1.2fr 1fr 1fr 1fr 1.5fr 1.5fr;
    gap:10px;
    padding:15px;
}
.table-header {
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;font-weight:600;
}
.table-row { border-bottom:1px solid #eee; }
.risk-badge {
    padding:6px 10px;border-radius:6px;
    font-size:12px;font-weight:600;display:inline-block;
}
.risk-critical { background:#ffe5e8;color:#f5576c; }
.risk-compliance { background:#fff4e5;color:#ffa502; }
.risk-wastage { background:#e3f2fd;color:#2196f3; }
.risk-normal { background:#e8f5e9;color:#28a745; }
.small-text { font-size:11px;color:#777;margin-top:4px; }
.hidden { display:none; }
</style>
</head>

<body>
<div class="container">
<h1 class="page-title">📋 License Overview (ML-Aligned)</h1>

<div class="filters-bar">
<button class="filter-btn active" data-filter="all">All</button>
<button class="filter-btn" data-filter="critical">🔴 Critical</button>
<button class="filter-btn" data-filter="compliance">🟠 Compliance</button>
<button class="filter-btn" data-filter="wastage">🔵 Wastage</button>
<button class="filter-btn" data-filter="normal">🟢 Normal</button>
</div>

<div class="table-container">
<div class="table-header">
<div>Software</div>
<div>Department</div>
<div>Total</div>
<div>Used</div>
<div>Unused %</div>
<div>Expiry</div>
<div>Status</div>
</div>

<?php foreach ($licenses_list as $l): ?>
<div class="table-row" data-risk="<?php echo $l['ml_class']; ?>">
<div>💾 <?php echo htmlspecialchars($l['software']); ?></div>
<div><?php echo htmlspecialchars($l['department']); ?></div>
<div><?php echo $l['total']; ?></div>
<div><?php echo $l['used']; ?></div>
<div><?php echo round($l['unused_percent'],1); ?>%</div>
<div>
<?php echo $l['expiry']; ?>
<div class="small-text"><?php echo $l['days_left']; ?> days</div>
</div>
<div>
<span class="risk-badge risk-<?php echo $l['ml_class']; ?>">
ML: <?php echo $l['ml_risk']; ?>
</span>
<div class="small-text">Rule: <?php echo $l['rule_risk']; ?></div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<script>
document.querySelectorAll('.filter-btn').forEach(btn=>{
btn.onclick=()=>{
document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
btn.classList.add('active');
let f=btn.dataset.filter;
document.querySelectorAll('.table-row').forEach(r=>{
if(f==="all"||r.dataset.risk===f) r.classList.remove('hidden');
else r.classList.add('hidden');
});
};
});
</script>
</body>
</html>
