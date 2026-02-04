<?php
// Mock parameters
$_GET['action'] = 'get_dashboard_data';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Capture output
ob_start();
include 'api.php';
$output = ob_get_clean();

echo "Output from api.php:\n";
echo $output;
echo "\n\nDecoded:\n";
print_r(json_decode($output, true));
?>
