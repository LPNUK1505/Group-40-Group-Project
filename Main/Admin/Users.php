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

// Handle delete action
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteQuery = "DELETE FROM User WHERE UserID = :userId";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bindValue(':userId', $deleteId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if ($result) {
        header("Location: Users.php?deleted=1");
        exit();
    } else {
        header("Location: Users.php?deleted=0");
        exit();
    }
}

// Pagination setup
$results_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Get total number of users
$countQuery = "SELECT COUNT(*) as total FROM User";
$countResult = $conn->query($countQuery);
$total_rows = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Fetch users with pagination
$query = "SELECT UserID, RoleID, AccountType, Firstname, Surname, Username, 
          DateOfBirth, Nationality, PhoneNumber, Email 
          FROM User 
          LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $results_per_page, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$result = $stmt->execute();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
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
            overflow-x: auto;
        }

        .users-container {
            margin: 20px 0;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #1e4e75;
        }

        .users-table th {
            background-color: #03588C;
            color: white;
            font-weight: 600;
        }

        .users-table tr:hover {
            background-color: rgba(3, 88, 140, 0.2);
        }

        .add-user-btn {
            display: inline-block;
            padding: 10px 15px;
            background: linear-gradient(90deg, #00c4ff, #0059a8);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin: 20px 0;
            transition: background 0.3s ease;
        }

        .add-user-btn:hover {
            background: linear-gradient(90deg, #009ecf, #004b91);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #1e4e75;
            margin: 0 4px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination a.active {
            background-color: #00c4ff;
            color: white;
            border: 1px solid #00c4ff;
        }

        .pagination a:hover:not(.active) {
            background-color: #03588C;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            display: none;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }

        .success {
            background-color: #2ecc71;
        }

        .error {
            background-color: #e74c3c;
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        @keyframes fadeOut {
            from {opacity: 1;}
            to {opacity: 0;}
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>User List</h1>
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

        <!-- Sidebar Buttons -->
        <div class="sidebar-separator"></div>
        <div class="sidebar-button active">
            <a href="Dashboard.php">
            <i class="fa-solid fa-house"></i></i>Dashboard
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Users.php">
                <i class="fa-solid fa-user-plus"></i>Users
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Teams.php">
            <i class="fa-solid fa-people-group"></i></i>Teams
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Matches.php">
                <i class="fa-solid fa-calendar"></i>Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="Pitches.php">
            <i class="fa-solid fa-street-view"></i></i>Pitches
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
    
    <!-- Main Content -->
    <div class="user-content">
        <?php 
        // Show notification if deleted
        if (isset($_GET['deleted'])) {
            if ($_GET['deleted'] == 1) {
                echo '<div class="notification success" id="notification">User deleted successfully!</div>';
            } else {
                echo '<div class="notification error" id="notification">Error deleting user!</div>';
            }
        }
        ?>
        
        <h2>Users List</h2>
        <div class="users-container">
            <table class="users-table">
                <tr>
                    <th>Account Type</th>
                    <th>First Name</th>
                    <th>Surname</th>
                    <th>Username</th>
                    <th>Date of Birth</th>
                    <th>Nationality</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['AccountType']); ?></td>
                        <td><?php echo htmlspecialchars($row['Firstname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Surname']); ?></td>
                        <td><?php echo htmlspecialchars($row['Username']); ?></td>
                        <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nationality']); ?></td>
                        <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="EditUser.php?id=<?php echo $row['UserID']; ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" class="delete-btn" 
                                   onclick="confirmDelete(<?php echo $row['UserID']; ?>)">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="Users.php?page=<?php echo $page - 1; ?>">&laquo;</a>
            <?php endif; ?>

            <?php 
            // Show page numbers
            $visible_pages = 5;
            $start = max(1, $page - floor($visible_pages / 2));
            $end = min($total_pages, $start + $visible_pages - 1);
            
            for ($i = $start; $i <= $end; $i++): ?>
                <a href="Users.php?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="Users.php?page=<?php echo $page + 1; ?>">&raquo;</a>
            <?php endif; ?>
        </div>

        <!-- Add User Button -->
        <a href="AddUser.php" class="add-user-btn">+ Add User</a>
    </div>

    <div class="footer">
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

    <script src="../sidebar.js"></script>
    <script>
        window.onload = function() {
            preventPageRefresh();
            
            // Show notification if it exists
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
        };
        
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                window.location.href = 'Users.php?delete_id=' + userId;
            }
        }
    </script>
</body>
</html>