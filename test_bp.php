<?php
// Test writing BP value
$test_data = [
    'bp' => '145/97',
    'timestamp' => date('Y-m-d H:i:s')
];

if (file_put_contents('bp_values.json', json_encode($test_data))) {
    echo "Successfully wrote test BP value<br>";
} else {
    echo "Failed to write test BP value<br>";
}

// Test reading BP value
if (file_exists('bp_values.json')) {
    $content = file_get_contents('bp_values.json');
    $data = json_decode($content, true);
    echo "Current BP value in file: " . ($data['bp'] ?? 'null') . "<br>";
} else {
    echo "bp_values.json does not exist<br>";
}
?> 