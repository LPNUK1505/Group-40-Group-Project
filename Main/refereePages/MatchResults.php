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
                <a href="../Homepages/SettingsData.php">Manage Data</a>
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
        <div class="sidebar-button" class="stayOnPageLink">
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
                <a href="../Homepages/SettingsPersonal.php">
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
        <a href="../Homepages/AboutUs.php">About Us</a>
        <a href="../Homepages/ContactUs.php">Contact Us</a>
    </div>

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

    /* Modal */
    #addNotesModal {
        display: none;
        position: fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background: rgba(0,0,0,0.6);
        z-index:1000;
        justify-content: center;
        align-items: center;
    }

    #addNotesModal form {
        background:white;
        padding:20px;
        border-radius:10px;
        width:300px;
        position:relative;
    }

    #addNotesModal h3 {
        color:rgb(0, 0, 0);
        margin-top: 0;
    }

    #addNotesModal label {
        color:rgb(0, 0, 0);
    }

    /* Add Notes button */
    .add-notes-btn {
        align-self: flex-end;
        background-color: #0056b3;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        margin-left: 10px;
    }

    .add-notes-btn:hover {
        background-color: #003d80;
    }
    </style>

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

    <div class="card-container">

        <!-- Premier League Results -->
        <div class="card">
            <h2>Premier League Results</h2>
            <?php
            $premQuery = "
                SELECT lm.LeagueMatchID, hm.TeamName AS HomeTeam, am.TeamName AS AwayTeam, 
                    lm.MatchDate, lm.HomeGoals, lm.AwayGoals
                FROM League_Match lm
                JOIN Team hm ON lm.HomeTeamID = hm.TeamID
                JOIN Team am ON lm.AwayTeamID = am.TeamID
                WHERE lm.LeagueID = 1
                AND lm.Status = 'Completed'
                ORDER BY lm.MatchDate DESC
                LIMIT 5
            ";
            $premResults = $conn->query($premQuery);

            while ($row = $premResults->fetchArray(SQLITE3_ASSOC)) {
                $homeTeam = htmlspecialchars($row['HomeTeam']);
                $awayTeam = htmlspecialchars($row['AwayTeam']);
                $homeGoals = intval($row['HomeGoals']);
                $awayGoals = intval($row['AwayGoals']);
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

        <!-- La Liga Results -->
        <div class="card">
            <h2>La Liga Results</h2>
            <?php
            $laLigaQuery = "
                SELECT lm.LeagueMatchID, hm.TeamName AS HomeTeam, am.TeamName AS AwayTeam, 
                    lm.MatchDate, lm.HomeGoals, lm.AwayGoals
                FROM League_Match lm
                JOIN Team hm ON lm.HomeTeamID = hm.TeamID
                JOIN Team am ON lm.AwayTeamID = am.TeamID
                WHERE lm.LeagueID = 2
                AND lm.Status = 'Completed'
                ORDER BY lm.MatchDate DESC
                LIMIT 5
            ";
            $laLigaResults = $conn->query($laLigaQuery);

            while ($row = $laLigaResults->fetchArray(SQLITE3_ASSOC)) {
                $homeTeam = htmlspecialchars($row['HomeTeam']);
                $awayTeam = htmlspecialchars($row['AwayTeam']);
                $homeGoals = intval($row['HomeGoals']);
                $awayGoals = intval($row['AwayGoals']);
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
                        <button class='add-notes-btn' data-matchid='{$leagueMatchID}'>Add Notes</button>
                    </div>";
            }
            ?>
        </div>

        </div>

        <!-- Modal for Add Notes -->
        <div id="addNotesModal">
            <form id="addNotesForm" method="POST" action="save_notes.php">
                <h3>Add Match Notes</h3>
                <input type="hidden" name="referee_id" value="3">
                <input type="hidden" name="league_match_id" id="leagueMatchIdInput">

                <label>Rating (1-10):</label><br>
                <select name="rating" required>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Notes (max 100 characters):</label><br>
                <textarea name="notes" maxlength="100" style="width:100%;" required></textarea><br><br>

                <button type="submit">Save</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </form>
        </div>
        <?php
        $dbInstance->closeConnection();
        ?>


        <script>
        // Open Modal
        document.querySelectorAll('.add-notes-btn').forEach(button => {
            button.addEventListener('click', function() {
                const matchId = this.getAttribute('data-matchid');
                document.getElementById('leagueMatchIdInput').value = matchId;
                document.getElementById('addNotesModal').style.display = 'flex';
            });
        });

        // Close Modal
        function closeModal() {
            document.getElementById('addNotesModal').style.display = 'none';
        }
        </script>
   </div>

        <!-- JAVA script to change colour of sidebar button referring to active page-->
        <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
        <script src="../sidebar.js"></script>
        <script>
            window.onload = preventPageRefresh;
        </script>

</html>