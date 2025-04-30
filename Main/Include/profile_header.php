<?php
// Include/profile_header.php

// Add memory limit at the very top
ini_set('memory_limit', '256M');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$userDisplay = ['name' => 'Guest', 'role' => 'Not Logged In'];
$dbInstance = null;

try {
    include_once __DIR__ . '/db.php';
    $dbInstance = new Database();
    $db = $dbInstance->getConnection();
    
    // Add SQLite performance optimizations
    $db->exec("PRAGMA journal_mode = WAL");
    $db->exec("PRAGMA synchronous = NORMAL");
    
    session_start();

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        // Get minimal user profile data needed for header
        $stmt = $db->prepare("
            SELECT u.Firstname, u.Surname, u.AccountType, 
                   t.TeamName
            FROM User u
            LEFT JOIN Player p ON u.UserID = p.UserID
            LEFT JOIN Team t ON p.TeamID = t.TeamID
            WHERE u.UserID = :user_id
            LIMIT 1
        ");
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            $userDisplay['name'] = $user['Firstname'] . ' ' . $user['Surname'];
            $userDisplay['role'] = ucfirst($user['AccountType']);
            
            if (strtolower($user['AccountType']) === 'player' && !empty($user['TeamName'])) {
                $userDisplay['role'] .= " - " . $user['TeamName'];
            }
        }
    }
} catch (Exception $e) {
    error_log("Profile Header Error: " . $e->getMessage());
    // Fail silently - will just show default "Guest" values
} finally {
    if ($dbInstance) $dbInstance->closeConnection();
}
?>

<div class="header">
    <div class="profile-box">
        <i class="fa fa-user"></i>
        <div>
            <div class="name"><?= htmlspecialchars($userDisplay['name']) ?></div>
            <div class="role"><?= htmlspecialchars($userDisplay['role']) ?></div>
        </div>
        <i class="fa fa-chevron-down dropdown-icon"></i>
        <div class="dropdown">
            <a href="#">Profile</a>
            <a href="../Homepages/SettingsData.php">Manage Data</a>
            <a href="../Create Account and Login/Login.php">Sign Out</a>
        </div>
    </div>
</div>