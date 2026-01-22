<?php
$logPath = __DIR__ . '/debug.log';
$result = file_put_contents($logPath, "Test write at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
if ($result === false) {
    echo "FAILED TO WRITE TO: " . $logPath;
} else {
    echo "SUCCESSFULLY WROTE TO: " . $logPath;
}
