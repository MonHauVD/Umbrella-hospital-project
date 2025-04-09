<?php
// Lấy thông tin user hoặc tên máy
$username = getenv('USERNAME');          // Windows user
$hostname = php_uname('n');              // Machine name

// Load cấu hình từ file
$config = require __DIR__ . '/LocalConfigDB.php'; // Điều chỉnh đường dẫn nếu cần

// In thông tin chi tiết
echo "=== System Info ===\n";
echo "Username (getenv): $username\n";
echo "Machine Name (php_uname): $hostname\n";

echo "\n=== Loaded Database Config ===\n";
echo "Database: " . $config['database'] . "\n";
echo "Frontend DB: " . $config['database_frontend'] . "\n";
echo "Host: " . $config['host'] . "\n";
echo "Charset: " . $config['charset'] . "\n";

// In toàn bộ mảng config (debug đầy đủ)
echo "\n=== Full Config Array ===\n";
print_r($config);
