<?php
// fix_permissions.php
// Script to recursively fix permissions
// Directories -> 0755
// Files -> 0644

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 300);

echo "<h1>Fixing Permissions...</h1>";
echo "<pre>";

$baseDir = __DIR__;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$countDirs = 0;
$countFiles = 0;

foreach ($iterator as $item) {
    // Skip this script itself to avoid locking issues (optional)
    if ($item->getFilename() === 'fix_permissions.php') continue;

    if ($item->isDir()) {
        if (chmod($item->getPathname(), 0755)) {
            // echo "DIR  0755: " . $item->getPathname() . "\n";
            $countDirs++;
        } else {
            echo "<span style='color:red'>FAIL DIR: " . $item->getPathname() . "</span>\n";
        }
    } else {
        if (chmod($item->getPathname(), 0644)) {
            // echo "FILE 0644: " . $item->getPathname() . "\n";
            $countFiles++;
        } else {
            echo "<span style='color:red'>FAIL FILE: " . $item->getPathname() . "</span>\n";
        }
    }
}

// Fix root dir as well
chmod($baseDir, 0755);

echo "</pre>";
echo "<h3>Done!</h3>";
echo "<p>Fixed $countDirs directories to 0755.</p>";
echo "<p>Fixed $countFiles files to 0644.</p>";
echo "<p><strong>Please delete this file (`fix_permissions.php`) after use.</strong></p>";
