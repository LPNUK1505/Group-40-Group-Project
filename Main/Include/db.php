<?php
class Database {
    private $db;
    private $dbPath;

    public function __construct($dbFile = 'Goikon.db') {
        // Absolute path to the database file
        $this->dbPath = __DIR__ . '/' . $dbFile;
        echo "Connecting to database at: $this->dbPath<br>"; // Debugging line

        // Try to connect to the database
        $this->db = new SQLite3($this->dbPath);

        if (!$this->db) {
            throw new Exception("Connection failed: " . $this->db->lastErrorMsg());
        }
    }

    public function getConnection() {
        return $this->db;
    }

    public function closeConnection() {
        $this->db->close();
    }
}

// Example of using the class:
try {
    // Create the database instance and get the connection
    $dbInstance = new Database();  // You can pass a different DB file name if needed
    $conn = $dbInstance->getConnection(); // Get the active connection
} catch (Exception $e) {
    // Handle the error (you can log the error instead of showing it in production)
    die("Error: " . $e->getMessage());
}

// Close the connection when done
$dbInstance->closeConnection();
?>