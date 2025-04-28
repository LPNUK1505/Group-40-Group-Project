<?php
class Database {
    private $db = null;
    private $dbPath;

    public function __construct() {
        $this->dbPath = __DIR__ . '/goikon_with_PL_standings.db';
        
        try {
            // Verify database file exists
            if (!file_exists($this->dbPath)) {
                throw new Exception("Database file not found at: " . $this->dbPath);
            }

            // Open database connection
            $this->db = new SQLite3($this->dbPath);
            $this->db->enableExceptions(true);

            // Verify connection is established
            if (!$this->db) {
                throw new Exception("Failed to connect to database");
            }

            // Verify essential tables exist
            $requiredTables = ['User', 'Player', 'Team'];
            foreach ($requiredTables as $table) {
                if (!$this->db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")) {
                    throw new Exception("Required table '$table' not found in database");
                }
            }

        } catch (Exception $e) {
            // Log the error and ensure $this->db remains null
            error_log("Database Error: " . $e->getMessage());
            $this->db = null;
            throw $e; // Re-throw for handling in calling code
        }
    }

    public function getConnection(): SQLite3 {
        if ($this->db === null) {
            throw new Exception("Database connection not established");
        }
        return $this->db;
    }

    public function closeConnection(): void {
        if ($this->db !== null) {
            $this->db->close();
            $this->db = null;
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }
}
?>