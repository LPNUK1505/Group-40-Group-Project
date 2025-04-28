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
        <div class="sidebar-button">
            <a href="AssignedMatches.php">
                <i class="fa-solid fa-user-plus"></i>Assigned Matches
            </a>
        </div>
        <div class="sidebar-button">
            <a href="MatchResults.php">
                <i class="fa-solid fa-square-check"></i>Match Results
            </a>
        </div>
        <div class="sidebar-button" class="stayOnPageLink">
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


    </script>
<!-- JAVA script to change colour of sidebar button referring to active page-->
<!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
<script src="../sidebar.js">
     window.onload = preventPageRefresh;
</script>
<style>
    /* Cards */
    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 20px;
    }

    .card {
        background-color: #062c50;
        color: white;
        border: 1px solid #114d8a;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 86, 179, 0.3);
    }

    .card h2 {
        text-align: center;
        color: #66ccff;
        margin-bottom: 20px;
    }

    .result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        background: #114d8a;
        padding: 8px 10px;
        border-radius: 8px;
    }

    .result span {
        font-size: 16px;
        font-weight: bold;
    }

    .result .winner {
        color: #66ff66;
    }
    </style>

   <!-- Creates Main Content area -->
   <div class="main-content">
       <h1><b>Misconduct Log</b></h1>

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
        <!-- Recent Officiated Matches -->
        <div class="card">
            <h2>Recent Officiated Matches</h2>
            <?php
            $officiatedQuery = "
                SELECT lm.LeagueMatchID, hm.TeamName AS HomeTeam, am.TeamName AS AwayTeam, 
                    lm.MatchDate, lm.HomeGoals, lm.AwayGoals
                FROM Referee_Booking rb
                JOIN League_Match lm ON rb.LeagueMatchID = lm.LeagueMatchID
                JOIN Team hm ON lm.HomeTeamID = hm.TeamID
                JOIN Team am ON lm.AwayTeamID = am.TeamID
                WHERE rb.RefereeID = 3
                AND lm.Status = 'Completed'
                ORDER BY lm.MatchDate DESC
                LIMIT 5
            ";
            $officiatedResults = $conn->query($officiatedQuery);

            while ($row = $officiatedResults->fetchArray(SQLITE3_ASSOC)) {
                $homeTeam = htmlspecialchars($row['HomeTeam']);
                $awayTeam = htmlspecialchars($row['AwayTeam']);
                $homeGoals = intval($row['HomeGoals']);
                $awayGoals = intval($row['AwayGoals']);
                $leagueMatchID = intval($row['LeagueMatchID']);
                $winnerHome = $homeGoals > $awayGoals ? 'winner' : '';
                $winnerAway = $awayGoals > $homeGoals ? 'winner' : '';

                echo "<div class='result'>
                        <span class='$winnerHome'>{$homeTeam} {$homeGoals}</span>
                        <span>-</span>
                        <span class='$winnerAway'>{$awayGoals} {$awayTeam}</span>
                    </div>";
            }
            ?>
        </div>
        <?php
        $dbInstance->closeConnection();
        ?>
   </div>
</html>