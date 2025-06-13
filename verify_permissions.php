<?php
// File path
$file = 'bp_values.json';

// Test writing
$test_data = [
    'bp' => '145/97',
    'timestamp' => date('Y-m-d H:i:s')
];

echo "<h3>File Information:</h3>";
echo "File exists: " . (file_exists($file) ? "Yes" : "No") . "<br>";
echo "Current permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "<br>";
echo "File owner: " . posix_getpwuid(fileowner($file))['name'] . "<br>";
echo "File group: " . posix_getgrgid(filegroup($file))['name'] . "<br>";

echo "<h3>Permission Tests:</h3>";
echo "Is readable: " . (is_readable($file) ? "Yes" : "No") . "<br>";
echo "Is writable: " . (is_writable($file) ? "Yes" : "No") . "<br>";

echo "<h3>Write Test:</h3>";
if (file_put_contents($file, json_encode($test_data))) {
    echo "Successfully wrote test data<br>";
} else {
    echo "Failed to write test data<br>";
}

echo "<h3>Read Test:</h3>";
if (file_exists($file)) {
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    echo "File contents: " . $content . "<br>";
    echo "Decoded BP value: " . ($data['bp'] ?? 'null') . "<br>";
} else {
    echo "File does not exist<br>";
}

// Try to force permissions
echo "<h3>Attempting to fix permissions:</h3>";
if (chmod($file, 0666)) {
    echo "Successfully set permissions to 666<br>";
    echo "New permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "<br>";
} else {
    echo "Failed to set permissions<br>";
}

// Show web server information
echo "<h3>Web Server Information:</h3>";
echo "Web server user: " . get_current_user() . "<br>";
echo "PHP process user: " . exec('whoami') . "<br>";
?> 