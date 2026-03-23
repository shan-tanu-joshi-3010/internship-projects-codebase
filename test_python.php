<?php
$python = "C:\\Users\\nikku\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
$script = "C:\\xampp\\htdocs\\license_tracker\\ml\\explain_risk.py";

// Redirect stderr to stdout (CRITICAL)
$cmd = "\"$python\" \"$script\" 100 40 60 20 1 2>&1";

$output = shell_exec($cmd);

echo "<pre>";
var_dump($output);
echo "</pre>";
?>
