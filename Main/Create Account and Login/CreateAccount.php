<?php
require_once '../Include/db.php';

$message = "";

// Only process the form if it was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username    = $_POST['username'];
    $password    = $_POST['password'];
    $firstname   = $_POST['firstname'];
    $lastname    = $_POST['lastname'];
    $email       = $_POST['email'];
    $role        = $_POST['role'];
    $nationality = $_POST['nationality'];
    $phoneNumber = $_POST['phonenumber'] ?? '';
    $dateOfBirth = $_POST['dateofbirth'] ?? '2000-01-01';
    $teamID      = $_POST['team_id'] ?? null; // New team selection field

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Get DB connection
        $dbInstance = new Database();
        $conn = $dbInstance->getConnection();

        // Begin transaction
        $conn->exec('BEGIN TRANSACTION');

        // Prepare the insert statement for User table
        $stmt = $conn->prepare("
            INSERT INTO User 
            (Username, Password, Firstname, Surname, Email, AccountType, Nationality, PhoneNumber, DateOfBirth, RoleID)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Map role to RoleID
        $roleIdMap = [
            'admin' => 1,
            'player' => 2,
            'team_manager' => 3,
            'referee' => 4
        ];
        $roleId = $roleIdMap[$role] ?? 2;

        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(3, $firstname, SQLITE3_TEXT);
        $stmt->bindValue(4, $lastname, SQLITE3_TEXT);
        $stmt->bindValue(5, $email, SQLITE3_TEXT);
        $stmt->bindValue(6, ucfirst($role), SQLITE3_TEXT);
        $stmt->bindValue(7, $nationality, SQLITE3_TEXT);
        $stmt->bindValue(8, $phoneNumber, SQLITE3_TEXT);
        $stmt->bindValue(9, $dateOfBirth, SQLITE3_TEXT);
        $stmt->bindValue(10, $roleId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            $newUserID = $conn->lastInsertRowID();
            
            // Handle role-specific assignments
            if ($role === 'player' && $teamID) {
                // Insert into Player table
                $stmt = $conn->prepare("
                    INSERT INTO Player (UserID, TeamID, Goals, Assists, RedCards, YellowCards, Appearances)
                    VALUES (?, ?, 0, 0, 0, 0, 0)
                ");
                $stmt->bindValue(1, $newUserID, SQLITE3_INTEGER);
                $stmt->bindValue(2, $teamID, SQLITE3_INTEGER);
                $stmt->execute();
            } 
            elseif ($role === 'team_manager' && $teamID) {
                // Insert into TeamManager table
                $stmt = $conn->prepare("
                    INSERT INTO TeamManager (UserID, StartDate)
                    VALUES (?, date('now'))
                ");
                $stmt->bindValue(1, $newUserID, SQLITE3_INTEGER);
                $stmt->execute();
                
                $newManagerID = $conn->lastInsertRowID();
                
                // Update Team table with new manager
                $stmt = $conn->prepare("
                    UPDATE Team SET ManagerID = ? WHERE TeamID = ?
                ");
                $stmt->bindValue(1, $newManagerID, SQLITE3_INTEGER);
                $stmt->bindValue(2, $teamID, SQLITE3_INTEGER);
                $stmt->execute();
            }
            
            // Commit transaction
            $conn->exec('COMMIT');
            
            // Close connection before redirecting
            $dbInstance->closeConnection();
            
            // Redirect to login page
            header("Location: Login.php");
            exit();
        } else {
            $message = "Failed to create account: " . $conn->lastErrorMsg();
            $conn->exec('ROLLBACK');
        }

        $dbInstance->closeConnection();

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        if (isset($conn)) $conn->exec('ROLLBACK');
    }
}

// Fetch teams for dropdown
$teams = [];
try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
    $result = $conn->query("SELECT TeamID, TeamName FROM Team ORDER BY TeamName");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $teams[] = $row;
    }
    $dbInstance->closeConnection();
} catch (Exception $e) {
    $message .= " Error loading teams: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        #team-selection {
            display: none;
            margin-top: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        #team-selection label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        #team-selection select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

<div class="header"></div>
<div class="sidebar">
    <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
    <div class="sidebar-separator"></div>
</div>
<div class="footer">
    <a href="AboutUs.html" class="stayOnPageLink">About Us</a>
    <a href="ContactUs.html">Contact Us</a>
</div>

<div class="main-content">
    <h1>Create Account</h1>
    <?php if (!empty($message)) echo "<p style='color: red;'>$message</p>"; ?>
    <form method="post" id="create-account-form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="nationality" placeholder="Nationality" required>
        <input type="tel" name="phonenumber" placeholder="Phone Number" required>
        <input type="date" name="dateofbirth" placeholder="Date of Birth" required>
        
        <select name="role" id="role-select" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="player">Player</option>
            <option value="team_manager">Team Manager</option>
            <option value="referee">Referee</option>
        </select>
        
        <div id="team-selection">
            <label for="team_id">Select Team:</label>
            <select name="team_id" id="team_id">
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team['TeamID'] ?>"><?= htmlspecialchars($team['TeamName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit">Create Account</button>
    </form>
</div>

<script>
document.getElementById('role-select').addEventListener('change', function() {
    const teamSelection = document.getElementById('team-selection');
    if (this.value === 'player' || this.value === 'team_manager') {
        teamSelection.style.display = 'block';
        document.getElementById('team_id').setAttribute('required', '');
    } else {
        teamSelection.style.display = 'none';
        document.getElementById('team_id').removeAttribute('required');
    }
});
</script>

</body>
</html>