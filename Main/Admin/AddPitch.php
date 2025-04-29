<?php
session_start();

// Check if user is logged in - ADDED MISSING SESSION CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Create Account and Login/Login.php");
    exit();
}

require_once '../Include/db.php';

// Get user info - ADDED USER INFO FETCHING
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Admin';

// Database connection - CHANGED TO USE $conn CONSISTENTLY
$dbInstance = new Database();
$conn = $dbInstance->getConnection();

// Get user details - ADDED USER DETAILS QUERY
$userQuery = $conn->prepare("SELECT Firstname, Surname FROM User WHERE UserID = ?");
$userQuery->bindValue(1, $user_id, SQLITE3_INTEGER);
$userResult = $userQuery->execute();
$userData = $userResult->fetchArray();

$userDisplay = [
    'name' => $userData ? htmlspecialchars($userData['Firstname'] . ' ' . $userData['Surname']) : 'Admin User',
    'role' => htmlspecialchars($user_role)
];

// Fetch field owners for dropdown
$fieldOwners = [];
$query = "SELECT FieldOwnerID, Firstname, Surname FROM FieldOwner";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $fieldOwners[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->exec("BEGIN TRANSACTION");

        // Insert new pitch/field
        $stmt = $conn->prepare("INSERT INTO Field (
            FieldOwnerID, 
            Name, 
            PricePerHour, 
            Location, 
            OpeningHours, 
            ClosingHours, 
            Capacity
        ) VALUES (
            :owner_id, 
            :name, 
            :price, 
            :location, 
            :opening_hours, 
            :closing_hours, 
            :capacity
        )");
        
        $stmt->bindParam(':owner_id', $_POST['owner_id']);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':price', $_POST['price']);
        $stmt->bindParam(':location', $_POST['location']);
        $stmt->bindParam(':opening_hours', $_POST['opening_hours']);
        $stmt->bindParam(':closing_hours', $_POST['closing_hours']);
        $stmt->bindParam(':capacity', $_POST['capacity']);
        
        $stmt->execute();

        // Commit transaction
        $conn->exec("COMMIT");

        echo "<script>alert('Pitch added successfully!'); window.location.href='Pitches.php';</script>";
    } catch (Exception $e) {
        // If any error occurs, rollback the transaction
        $conn->exec("ROLLBACK");
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Pitch</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Add Pitch</h2>
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
    
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
        <div class="sidebar-separator"></div>
        <div class="sidebar-button">
            <a href="Pitches.php">
                <i class="fa-solid fa-arrow-left"></i>Go Back
            </a>
        </div>
        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.php">
                    <i class="fa-solid fa-gear"></i>Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i>Sign Out
                </a>
            </div>
        </div>
    </div>
    
    <style>
    .player-content {
        position: fixed;
        align-content: left;
        top: 100px;
        left: 250px;
        width: calc(100vw - 250px);
        height: calc(100vh - 164px);
        background-color: #022340;
        z-index: 1000001;
        font-family:Verdana, Tahoma, sans-serif;
    }

    .player-content button {
        background-color: #03588C;
        color: #F2F2F2;
        text-align: center;
        font-weight: bold;
        text-decoration: none;
        font-size: 18px;
        width: 230px;
        display: flex;
        padding: 10px 0;
        display: block;
        border: 3px solid white;
        border-radius: 50px;
        margin: 0 auto;
    }
    .player-content::-webkit-scrollbar {
        width: 8px;
    }
    .player-content::-webkit-scrollbar-thumb {
        background-color: #1e4e75;
        border-radius: 10px;
    }

    .player-content {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        max-height: calc(100vh - 70px);
        box-sizing: border-box;
    }

    .player-content h1{
        text-align: center;
        font-size: 40px;
        color: #F2F2F2;
        cursor: pointer;
    }

    .player-content p{
        text-align: center;
        font-size: 20px;
        color: #f2f2f2;
        font:600;
        line-height: normal;
    }

    .player-content h3{
        text-align: center;
        font-size: 28px;
        color: #f2f2f2;
        font:600;
        height: 100px;
    }

    .player-content ol{
        text-align: center;
        font-size: 20px;
        color: #f2f2f2;
        font:600;
    }

    .player-content table, th, td{
        border: 1px solid white;
        border-collapse: collapse;
        color: #F2F2F2;
        text-align: center;
        margin-left: auto;
        margin-right: auto;
    }

    .player-content a{
        background-color: #03588C;
        color: #F2F2F2;
        text-align: center;
        font-size: 18px;
        width: 250px;
        display: flex;
        padding: 10px;
        display: block;
        margin: auto;
        text-decoration: none;
    }

    .form-container {
        background: #024873;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        width: 350px;
        text-align: center;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .player-content label {
        font-weight: bold;
        display: block;
        margin: 10px 0 5px;
        text-align: left;
        color: white;
    }

    .player-content input, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        background-color: #e9f1f9;  
        color: #333;  
    }
    
    .player-form-wrapper {
        background: linear-gradient(145deg, #002b44, #004c70);
        max-width: 650px;
        margin: 40px auto;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        color: #f1f1f1;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .player-form-wrapper h1 {
        text-align: center;
        font-size: 28px;
        color: #ffffff;
        margin-bottom: 30px;
        text-shadow: 1px 1px 3px #000;
    }

    .player-form-wrapper form {
        display: flex;
        flex-direction: column;
    }

    .player-form-wrapper label {
        font-size: 15px;
        margin-bottom: 5px;
        margin-top: 15px;
        color: #d2e9ff;
        font-weight: 600;
    }

    .player-form-wrapper input,
    .player-form-wrapper select {
        padding: 12px;
        border-radius: 8px;
        border: none;
        font-size: 15px;
        background-color: #e9f3fb;
        margin-bottom: 10px;
        transition: border 0.2s ease;
    }

    .player-form-wrapper input:focus,
    .player-form-wrapper select:focus {
        border: 2px solid #00c4ff;
        outline: none;
    }

    .player-form-wrapper button[type="submit"] {
        margin-top: 20px;
        padding: 14px;
        background: linear-gradient(90deg, #00c4ff, #0059a8);
        border: none;
        color: white;
        font-size: 16px;
        font-weight: bold;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .player-form-wrapper button[type="submit"]:hover {
        background: linear-gradient(90deg, #009ecf, #004b91);
    }
    
    .time-input {
        display: flex;
        align-items: center;
    }
    
    .time-input input {
        flex: 1;
    }
    
    .time-separator {
        margin: 0 5px;
        color: white;
    }
</style>

    <!-- Main Content -->
    <div class="player-content">
        <div class="player-form-wrapper">
            <h1>Add New Pitch</h1>
            <form method="post" action="AddPitch.php">
                <label for="owner_id">Field Owner:</label>
                <select id="owner_id" name="owner_id" required>
                    <?php foreach ($fieldOwners as $owner): ?>
                        <option value="<?php echo htmlspecialchars($owner['FieldOwnerID']); ?>">
                            <?php echo htmlspecialchars($owner['Firstname'] . ' ' . $owner['Surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="name">Pitch Name:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="price">Price Per Hour ($):</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
                
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
                
                <label for="opening_hours">Opening Time:</label>
                <input type="time" id="opening_hours" name="opening_hours" required>
                
                <label for="closing_hours">Closing Time:</label>
                <input type="time" id="closing_hours" name="closing_hours" required>
                
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" min="1" required>
                
                <button type="submit">Add Pitch</button>
            </form>
        </div>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
        
        function preventPageRefresh() {
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>