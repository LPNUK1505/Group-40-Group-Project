<!DOCTYPE html>
<html>
    <!-- JAVA line for 'fontawesome' icons -->
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <!-- Creates the Header at the top -->
    <div class="header">
        <div class="profile-box">
            <i class="fa fa-user"></i>
            <div>
                <div class="name">John Doe</div>
                <div class="role">Team Manager</div>
            </div>
            <i class="fa fa-chevron-down dropdown-icon"></i>
            <div class="dropdown">
                <a href="#">Profile</a>
                <a href="../Homepages/SettingsData.html">Manage Data</a>
                <a href="../Create Account and Login/Login.php">Sign Out</a>
            </div>
        </div>
    </div>
    
    <!-- Creates the Sidebar on the left hand side -->
    <div class="sidebar">
        <img src="../GoikonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">
        <!-- Creates buttons in the Sidebar -->
        <div class="sidebar-separator"></div>

        <div class="sidebar-button">
            <a href="RefereeDashboard.php">
                <i class="fa-solid fa-people-group"></i>Dashboard
            </a>
        </div>
        <div class="sidebar-button" class="stayOnPageLink">
            <a href="AssignedMatches.php">
                <i class="fa-solid fa-user-plus"></i>Assigned Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="MatchResults.php">
                <i class="fa-solid fa-square-check"></i>Match Results
            </a>
        </div>
        <div class="sidebar-button">
            <a href="MisconductLog.php">
                <i class="fa-solid fa-chart-simple"></i>Misconduct Log
            </a>
        </div>

        <div class="sidebar-toolbox-container">
            <div class="sidebar-separator"></div>
            <div class="sidebar-toolbox-button">
                <a href="../Homepages/SettingsPersonal.html">
                    <i class="fa-solid fa-gear"></i>Settings
                </a>
            </div>
            <div class="sidebar-toolbox-button">
                <a href="../Create Account and Login/Login.php">
                    <i class="fa-solid fa-right-from-bracket"></i></i>Sign Out
                </a>
            </div>
        </div>
    </div>
    <!-- Creates the Footer at the bottom -->
    <div class="footer">
        <!-- Creates buttons in the footer -->
        <a href="../Homepages/AboutUs.html">About Us</a>
        <a href="../Homepages/ContactUs.html">Contact Us</a>
    </div>


    <style>
    .fixtures-container {
        padding: 10px;
    }

    .team-list-header h2 {
        color: #3399ff; 
        text-align: center;
        margin-bottom: 20px;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .fixture-card {
        background-color: #062c50;
        border: 1px solid #114d8a;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0, 86, 179, 0.3);
        transition: transform 0.3s, box-shadow 0.3s;
        color: #ffffff; 
        font-size: 16px;
        height: auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .fixture-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(51, 153, 255, 0.5);
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
        gap: 30px;  
        height: calc(100% - 100px);
        overflow: hidden;
    }


    .fixture-teams {
        font-size: 20px; 
        font-weight: bold;
        color: #66ccff;  
        margin-bottom: 15px;
        display: flex;  
        justify-content: center; 
        align-items: center; 
    }
    .fixture-teams span {
        white-space: nowrap;
    }

    .fixture-teams .home-team {
        margin-right: 10px; 
        color: #ff6600;
    }

    .fixture-teams .away-team {
        margin-left: 10px;
        color: #00cc99; 
    }

    .fixture-teams i {
        font-size: 22px; 
        color: #ffffff; 
        margin: 0 10px;  
    }

    .fixture-date, .fixture-stadium {
        font-size: 14px;
        color: #cce6ff; 
        margin-top: 10px; 
        text-align: center; 
    }

    .fixture-stadium i {
        margin-right: 5px;
        color: #66ccff;  
    }

    .pagination-buttons {
        margin-top: 30px; 
        text-align: center;
    }

    .pagination-buttons button {
        background-color: #0056b3;
        color: white;
        border: none;
        padding: 12px 25px;
        margin: 0 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px; 
        transition: background-color 0.3s;
    }

    .pagination-buttons button:hover {
        background-color: #003d80;
    }

    .pagination-buttons button:disabled {
        background-color: #ccc;
        cursor: default;
    }
</style>

<script>
window.onload = function() {
    const cardsPerPage = 6;
    let currentPage = 1;

    const cards = document.querySelectorAll('.fixture-card');
    const totalPages = Math.ceil(cards.length / cardsPerPage);

    // Function to display cards for the current page
    function showPage(page) {
        cards.forEach((card, index) => {
            // Only show the cards for the current page
            card.style.display = (index >= (page - 1) * cardsPerPage && index < page * cardsPerPage) ? 'block' : 'none';
        });

        // Disable or enable pagination buttons
        document.getElementById('prevPage').disabled = (page === 1);
        document.getElementById('nextPage').disabled = (page === totalPages);
    }

    // Handle previous page click
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    // Handle next page click
    document.getElementById('nextPage').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
        }
    });

    showPage(currentPage);
};

</script>
<!-- JAVA script to change colour of sidebar button referring to active page-->
<!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
<script src="../sidebar.js">
     window.onload = preventPageRefresh;
</script>


   <!-- Creates Main Content area -->
   <div class="main-content">
        <?php
        require_once __DIR__ . '/../Include/db.php';
        // connect to db
        try {
            $dbInstance = new Database();
            $conn = $dbInstance->getConnection();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
        ?>

        <?php
        $query = "
            SELECT 
                hm.TeamName AS HomeTeam, 
                am.TeamName AS AwayTeam, 
                lm.MatchDate,
                f.Name AS Stadium
            FROM League_Match lm
            JOIN Team hm ON lm.HomeTeamID = hm.TeamID
            JOIN Team am ON lm.AwayTeamID = am.TeamID
            JOIN Field f ON hm.FieldID = f.FieldID
            JOIN Referee_Booking rb ON lm.LeagueMatchID = rb.LeagueMatchID
            WHERE lm.Status = 'Scheduled'
            AND rb.RefereeID = 3
            ORDER BY lm.MatchDate ASC
        ";

        $result = $conn->query($query);
        ?>

        <div class="fixtures-container">
            <div class="team-list-header">
                <h2>Your Upcoming Fixtures</h2>
            </div>

            <div class="cards-grid" id="cardsGrid">
                <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                    <div class="fixture-card">
                        <h3 class="fixture-teams">
                            <span class="home-team"><?php echo htmlspecialchars($row['HomeTeam']); ?></span>
                            <i class="fa-solid fa-v"></i> 
                            <span class="away-team"><?php echo htmlspecialchars($row['AwayTeam']); ?></span>
                        </h3>
                        <p class="fixture-date">
                            <?php 
                                $datetime = date("F j, Y - H:i", strtotime($row['MatchDate']));
                                echo htmlspecialchars($datetime); 
                            ?>
                        </p>
                        <p class="fixture-stadium">
                            <i class="fa-solid fa-location-dot"></i> 
                            <?php echo htmlspecialchars($row['Stadium']); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="pagination-buttons">
                <button id="prevPage" disabled>Previous</button>
                <button id="nextPage">Next</button>
            </div>
        </div>

        <?php
        $dbInstance->closeConnection();
        ?>
    </div>
</html>