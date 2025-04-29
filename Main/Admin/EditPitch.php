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

$fieldId = $name = $pricePerHour = $location = $openingHours = $closingHours = $capacity = '';
$ownerFirstname = $ownerSurname = $ownerEmail = $fieldOwnerId = '';
$owners = [];
$error = '';

// Fetch all field owners for dropdown
$ownerQuery = "SELECT FieldOwnerID, Firstname, Surname, Email FROM FieldOwner";
$ownerResult = $conn->query($ownerQuery);
if ($ownerResult) {
    while ($row = $ownerResult->fetchArray(SQLITE3_ASSOC)) {
        $owners[] = $row;
    }
}

// Check if pitch ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: Pitches.php");
    exit();
}

$fieldId = $_GET['id'];

// Fetch pitch data
$query = "SELECT 
            f.FieldID,
            f.Name,
            f.PricePerHour,
            f.Location,
            f.OpeningHours,
            f.ClosingHours,
            f.Capacity,
            fo.Firstname AS OwnerFirstname,
            fo.Surname AS OwnerSurname,
            fo.Email AS OwnerEmail,
            f.FieldOwnerID
          FROM Field f
          JOIN FieldOwner fo ON f.FieldOwnerID = fo.FieldOwnerID
          WHERE f.FieldID = :fieldId";

$stmt = $conn->prepare($query);
$stmt->bindValue(':fieldId', $fieldId, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row) {
    header("Location: Pitches.php?error=pitch_not_found");
    exit();
}


$name = $row['Name'];
$pricePerHour = $row['PricePerHour'];
$location = $row['Location'];
$openingHours = $row['OpeningHours'];
$closingHours = $row['ClosingHours'];
$capacity = $row['Capacity'];
$ownerFirstname = $row['OwnerFirstname'];
$ownerSurname = $row['OwnerSurname'];
$ownerEmail = $row['OwnerEmail'];
$fieldOwnerId = $row['FieldOwnerID'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $pricePerHour = trim($_POST['price_per_hour']);
    $location = trim($_POST['location']);
    $openingHours = trim($_POST['opening_hours']);
    $closingHours = trim($_POST['closing_hours']);
    $capacity = trim($_POST['capacity']);
    $ownerId = trim($_POST['owner_id']);


    if (empty($name) || empty($pricePerHour) || empty($location) || empty($openingHours) || empty($closingHours) || empty($capacity)) {
        $error = "All fields are required!";
    } elseif (!is_numeric($pricePerHour)) {
        $error = "Price per hour must be a number!";
    } elseif (!is_numeric($capacity)) {
        $error = "Capacity must be a number!";
    } else {
        // Update the pitch in the database
        $updateQuery = "UPDATE Field SET 
                        Name = :name,
                        PricePerHour = :pricePerHour,
                        Location = :location,
                        OpeningHours = :openingHours,
                        ClosingHours = :closingHours,
                        Capacity = :capacity,
                        FieldOwnerID = :ownerId
                        WHERE FieldID = :fieldId";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':pricePerHour', $pricePerHour, SQLITE3_FLOAT);
        $stmt->bindValue(':location', $location, SQLITE3_TEXT);
        $stmt->bindValue(':openingHours', $openingHours, SQLITE3_TEXT);
        $stmt->bindValue(':closingHours', $closingHours, SQLITE3_TEXT);
        $stmt->bindValue(':capacity', $capacity, SQLITE3_INTEGER);
        $stmt->bindValue(':ownerId', $ownerId, SQLITE3_INTEGER);
        $stmt->bindValue(':fieldId', $fieldId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            header("Location: Pitches.php?updated=1");
            exit();
        } else {
            $error = "Error updating pitch: " . $conn->lastErrorMsg();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Pitch</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .user-content {
            position: relative;
            margin-left: 250px;
            padding: 20px;
            min-height: calc(100vh - 160px);
            background-color: #022340;
            color: #F2F2F2;
            font-family: Verdana, Tahoma, sans-serif;
        }

        .user-form-wrapper {
            background: linear-gradient(145deg, #002b44, #004c70);
            max-width: 650px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #f1f1f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .user-form-wrapper h1 {
            text-align: center;
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px #000;
        }

        .user-form-wrapper form {
            display: flex;
            flex-direction: column;
        }

        .user-form-wrapper label {
            font-size: 15px;
            margin-bottom: 5px;
            margin-top: 15px;
            color: #d2e9ff;
            font-weight: 600;
        }

        .user-form-wrapper input,
        .user-form-wrapper select {
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            background-color: #e9f3fb;
            margin-bottom: 10px;
            transition: border 0.2s ease;
        }

        .user-form-wrapper input:focus,
        .user-form-wrapper select:focus {
            border: 2px solid #00c4ff;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-save {
            background: linear-gradient(90deg, #00c4ff, #0059a8);
            color: white;
        }

        .btn-save:hover {
            background: linear-gradient(90deg, #009ecf, #004b91);
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        .error-message {
            color: #ff6b6b;
            background-color: rgba(255, 107, 107, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ff6b6b;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .user-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .user-form-wrapper {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h2>Edit Pitch</h2>
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
                <i class="fa-solid fa-arrow-left"></i> Go Back
            </a>
        </div>
        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.php">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="user-content">
        <div class="user-form-wrapper">
            <h1>Edit Pitch</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <label for="name">Pitch Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                
                <label for="price_per_hour">Price Per Hour ($):</label>
                <input type="number" step="0.01" id="price_per_hour" name="price_per_hour" value="<?php echo htmlspecialchars($pricePerHour); ?>" required>
                
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>
                
                <label for="opening_hours">Opening Time:</label>
                <input type="time" id="opening_hours" name="opening_hours" value="<?php echo htmlspecialchars(substr($openingHours, 0, 5)); ?>" required>
                
                <label for="closing_hours">Closing Time:</label>
                <input type="time" id="closing_hours" name="closing_hours" value="<?php echo htmlspecialchars(substr($closingHours, 0, 5)); ?>" required>
                
                <label for="capacity">Capacity:</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($capacity); ?>" required>
                
                <label for="owner_id">Owner:</label>
                <select id="owner_id" name="owner_id" required>
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?php echo htmlspecialchars($owner['FieldOwnerID']); ?>"
                            <?php echo $owner['FieldOwnerID'] == $fieldOwnerId ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($owner['Firstname'] . ' ' . $owner['Surname'] . ' (' . $owner['Email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div class="form-actions">
                    <a href="Pitches.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = function() {
            preventPageRefresh();
        };
        
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