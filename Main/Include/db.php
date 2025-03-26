<?php
$database = new SQLite3('Goikon.db'); 


if (!$database) {
    die("Connection failed: " . $database->lastErrorMsg());
} else {
    echo "Connected to SQLite successfully!";
}
?>