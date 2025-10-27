<?php

/**
 * Manual Database Update Script for FreePOS
 * Run this script to apply pending database updates
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load application bootstrap to set up config and helpers
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Utility\DbUpdater;

echo "FreePOS Database Update Script\n";
echo "==============================\n\n";

$updater = new DbUpdater();
$result = $updater->upgrade('1.4.4', false); // false = no auth needed

echo "\nResult: " . $result . "\n";

if (strpos($result, 'completed') !== false) {
    echo "\nDatabase update completed successfully!\n";
} else {
    echo "\nDatabase update failed or no updates needed.\n";
}