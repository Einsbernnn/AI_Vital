<?php
// File path
$file = 'bp_values.json';

// Check if file exists
if (file_exists($file)) {
    echo "File exists: Yes<br>";
    
    // Get current permissions
    $perms = fileperms($file);
    echo "Current permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
    
    // Get file owner and group
    echo "File owner: " . posix_getpwuid(fileowner($file))['name'] . "<br>";
    echo "File group: " . posix_getgrgid(filegroup($file))['name'] . "<br>";
    
    // Check if web server can read/write
    echo "Web server can read: " . (is_readable($file) ? "Yes" : "No") . "<br>";
    echo "Web server can write: " . (is_writable($file) ? "Yes" : "No") . "<br>";
    
    // Try to set permissions to 666 (read/write for all)
    if (chmod($file, 0666)) {
        echo "Successfully set permissions to 666<br>";
        echo "New permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "<br>";
    } else {
        echo "Failed to set permissions<br>";
    }
} else {
    echo "File does not exist. Creating it...<br>";
    
    // Create file with initial data
    $initial_data = json_encode(['bp' => null, 'timestamp' => date('Y-m-d H:i:s')]);
    if (file_put_contents($file, $initial_data)) {
        echo "File created successfully<br>";
        chmod($file, 0666);
        echo "Permissions set to 666<br>";
    } else {
        echo "Failed to create file<br>";
    }
}

// Display web server user
echo "<br>Web server user: " . get_current_user() . "<br>";
?> 