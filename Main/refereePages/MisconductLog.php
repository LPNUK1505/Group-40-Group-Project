<?php include_once __DIR__ . '/../Include/profile_header.php'; ?>
<!DOCTYPE html>
<html>
    <!-- JAVA line for 'fontawesome' icons -->
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
    <!-- Creates the Header at the top -->
   
    
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


    </script>
<!-- JAVA script to change colour of sidebar button referring to active page-->
<!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
<script src="../sidebar.js">
     window.onload = preventPageRefresh;
</script>
<style>
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
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }
    
    .result-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #ddd;
    }

    .team {
        font-weight: bold;
        text-align: center;
        padding: 5px;
    }

    .home {
        text-align: right; 
    }

    .away {
        text-align: left; 
    }

    .score {
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        min-width: 60px;
    }

    .winner {
        color: #4CAF50;
    }

    #misconductModal {
        display: none;
        position: fixed;
        top:0; left:0;
        width:100%; height:100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
    }

    #misconductModal form {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        color: black;
    }

    .report-misconduct-btn {
        padding: 4px 8px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .report-misconduct-btn:hover {
        background-color: #c82333;
    }

    #misconductModal h3 {
        color:rgb(0, 0, 0);
        margin-top: 0;
    }

    #misconductModal label {
        color:rgb(0, 0, 0);
    }

    .winner {
        color: green;
        font-weight: bold;
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
        <!-- Recent Matches with Misconduct Button -->
        <div class="card">
            <h2>Recent Officiated Matches</h2>
            <?php
            $officiatedQuery = "
                SELECT lm.LeagueMatchID, hm.TeamID AS HomeID, am.TeamID AS AwayID,
                    hm.TeamName AS HomeTeam, am.TeamName AS AwayTeam, 
                    lm.MatchDate, lm.HomeGoals, lm.AwayGoals
                FROM Referee_Booking rb
                JOIN League_Match lm ON rb.LeagueMatchID = lm.LeagueMatchID
                JOIN Team hm ON lm.HomeTeamID = hm.TeamID
                JOIN Team am ON lm.AwayTeamID = am.TeamID
                WHERE rb.RefereeID = 3 AND lm.Status = 'Completed'
                ORDER BY lm.MatchDate DESC
                LIMIT 5
            ";
            $officiatedResults = $conn->query($officiatedQuery);

            while ($row = $officiatedResults->fetchArray(SQLITE3_ASSOC)) {
                $leagueMatchID = intval($row['LeagueMatchID']);
                $homeTeam = htmlspecialchars($row['HomeTeam']);
                $awayTeam = htmlspecialchars($row['AwayTeam']);
                $homeID = intval($row['HomeID']);
                $awayID = intval($row['AwayID']);
                $homeGoals = intval($row['HomeGoals']);
                $awayGoals = intval($row['AwayGoals']);
                $winnerHome = $homeGoals > $awayGoals ? 'winner' : '';
                $winnerAway = $awayGoals > $homeGoals ? 'winner' : '';

                echo "<div class='result-row'>
                        <div class='team home $winnerHome'>{$homeTeam}</div>
                        <div class='score'>{$homeGoals} - {$awayGoals}</div>
                        <div class='team away $winnerAway'>{$awayTeam}</div>
                        <div class='notes-button'>
                            <button class='report-misconduct-btn' 
                                    data-matchid='{$leagueMatchID}' 
                                    data-homeid='{$homeID}' 
                                    data-awayid='{$awayID}' 
                                    data-homename='{$homeTeam}' 
                                    data-awayname='{$awayTeam}'>
                                Report Misconduct
                            </button>
                        </div>
                    </div>";
            }
            ?>
        </div>

        <!-- Modal: Misconduct -->
        <div id="misconductModal" style="display:none;">
            <form id="misconductForm" method="POST" action="save_misconduct.php">
                <h3>Report Misconduct</h3>
                <input type="hidden" name="referee_id" value="3">
                <input type="hidden" name="league_match_id" id="misconductMatchId">

                <label>Team:</label><br>
                <select name="team_id" id="teamSelect" required></select><br><br>

                <label>Player ID (1-12):</label><br>
                <input type="number" name="player_id" min="1" max="12" required><br><br>

                <label>Player Number (1-99):</label><br>
                <input type="number" name="player_number" min="1" max="99" required><br><br>

                <label>Event:</label><br>
                <select name="event" required>
                    <option value="Yellow Card">Yellow Card</option>
                    <option value="Red Card">Red Card</option>
                </select><br><br>

                <label>Time (1-90):</label><br>
                <input type="number" name="time" min="1" max="90" required><br><br>

                <label>Reason (max 30 chars):</label><br>
                <input type="text" name="reason" maxlength="30" required><br><br>

                <button type="submit">Submit</button>
                <button type="button" onclick="closeMisconductModal()">Cancel</button>
            </form>
        </div>

        <script>
        document.querySelectorAll('.report-misconduct-btn').forEach(button => {
            button.addEventListener('click', function () {
                const matchId = this.dataset.matchid;
                const homeId = this.dataset.homeid;
                const awayId = this.dataset.awayid;
                const homeName = this.dataset.homename;
                const awayName = this.dataset.awayname;

                document.getElementById('misconductMatchId').value = matchId;

                const teamSelect = document.getElementById('teamSelect');
                teamSelect.innerHTML = `
                    <option value="${homeId}">${homeName}</option>
                    <option value="${awayId}">${awayName}</option>
                `;

                document.getElementById('misconductModal').style.display = 'flex';
            });
        });

        function closeMisconductModal() {
            document.getElementById('misconductModal').style.display = 'none';
        }
        </script>
   </div>
</html>