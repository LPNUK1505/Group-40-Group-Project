<!DOCTYPE html>
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
            <a href="RefereeDashboard.php" class="stayOnPageLink">
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

    <!-- Creates Main Content area -->
    <div class="main-content">
        <style>
            .dashboard {
                display: grid;
                grid-template-columns: repeat(12, 1fr);
                grid-template-rows: repeat(4, 1fr);
                height: calc(100% - 100px);
                gap: 15px;
                padding: 5px 15px 15px 15px;
                box-sizing: border-box;
                overflow: hidden;
            }

            #standings-prem { 
                grid-column: 1 / 4;
                grid-row: 1 / 5;
            }

            #standings-la-liga {
                grid-column: 10 / 13;
                grid-row: 1 / 5;
            }

            #fixtures {
                grid-column: 4 / 7;
                grid-row: 1 / 3;
            }

            #referee-resources {
                grid-column: 7 / 10;
                grid-row: 1 / 3;
            }

            #booking-points {
                grid-column: 4 / 7;
                grid-row: 3 / 5;
            }

            #certificate {
                grid-column: 7 / 10;
                grid-row: 3 / 5;
            }

            .card {
                display: flex;
                flex-direction: column;
                background: linear-gradient(135deg, #1b3d55, #022340);
                padding: 0px 20px;
                border-radius: 15px;
                box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2);
                color: #FFFFFF;
                box-sizing: border-box;
                overflow: hidden;
                border: 3px solid #044D8C;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 4px 8px 25px rgba(0, 0, 0, 0.3);
            }

            ul {
                list-style: none;
                padding: 0;
            }

            ul li {
                padding: 5px 0;
            }

            .scrollable-container {
                overflow-y: auto;
            }

            .scrollable-container::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .squad-summary-table {
                position: sticky;
                width: 100%;
                margin: 20px;
                border-collapse: collapse;
                background-color: rgba(255, 255, 255, 0.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                overflow: hidden;
            }

            .squad-summary-table th {
                background-color: #0E304A;
                color: #F2F2F2;
                font-weight: bold;
                text-transform: uppercase;
            }

            .squad-summary-table tr:nth-child(even) {
                background-color: #1b3d55;
            }

            .squad-summary-table tr:hover {
                background-color: rgba(255, 255, 255, 0.1);
                transition: 0.3s ease-in-out;
            }

            table {
                position: sticky;
                width: 100%;
                margin: 20px;
                border-collapse: collapse;
                background-color: #024873;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 10px;
                overflow: hidden;
            }

            table th, table td {
                padding: 12px;
                border: 1px solid white;
                text-align: center;
            }

            table th {
                background-color: #03588C;
                color: #F2F2F2;
                font-weight: bold;
                text-transform: uppercase;
            }

            table tr:nth-child(even) {
                background-color: #0271A1;
            }

            table tr:hover {
                background-color: #046b9d;
                transition: 0.3s ease-in-out;
            }

            #friendly button {
                border: none;
                cursor: pointer;
                font-size: 18px;
                padding: 5px;
                border-radius: 5px;
                width: 32px;
                height: 32px;
                text-align: center;
                line-height: 1;
            }

            .team-list {
                list-style: none;
                padding-left: 0;
                margin-top: 10px;
            }

            .team-list-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .team-list-header a{
                display: flex;
                align-items: center;
                width: min-content;
                font-size: 15px;
                gap: 5px;
                color: white;
                font-weight: bold;
                border: none;
                border-radius: 20px;
                padding: 5px 10px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .team-list-header a:hover {
                background-color: #024873;
            }

            .team-list-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px;
                margin: 10px 0;
                background-color: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: background-color 0.3s ease, transform 0.3s ease;
            }

            .booking-action-button {
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: bold;
                cursor: pointer;
                border: none;
                transition: background-color 0.3s ease;
            }

            .booking-action-button.accept {
                background-color: #28a745;
                color: white;
            }

            .booking-action-button.accept:hover {
                background-color: #218838;
            }

            .booking-action-button.deny {
                background-color: #dc3545;
                color: white;
            }

            .booking-action-button.deny:hover {
                background-color: #c82333;
            }

            .dashboard h2{
                position: sticky;
                width: 100%;
                top: 0;
                text-align: left;
                color: #f2f2f2;
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 10px;
                letter-spacing: 1px;
                text-transform: uppercase;
                z-index: 1;
            }

            .team-list-item-text-container {
                display: grid;
                flex-direction: row;
                width: 100%;
            }

            .match-date-time {
                font-size: 14px;
                color: #E1E1E1;
                margin-left: 15px;
            }

            .match-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-right: 10px;
            }

            .match-score.win {
                font-size: 18px;
                font-weight: bold;
                color: #28a745;
            }

            .match-score.draw {
                font-size: 18px;
                font-weight: bold;
                color: #ffc107;
            }

            .match-score.lose {
                font-size: 18px;
                font-weight: bold;
                color: #dc3545;
            }

            .fixture-list {
                width: 100%;
                list-style: none;
                margin-top: 0;
            }

            .fixture-list-item {
                width: 100%;
                margin: 12px 0;
                border-radius: 10px;
                background-color: #1b3d55;
            }

            .fixture-list-item-text-container {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .fixture-list a{
                width: calc(100% - 24px);
                background-color: inherit;
            }

            .fixture-list-item-additional{
                gap: 15px;
                margin-left: 15px;
            }

            .fixture-list-item:hover {
                background: linear-gradient(135deg, #224766, #12314a);
                transform: scale(1.02);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            }

        </style>

        <h1> Dashboard </h1>
        <div class="dashboard">

            <div class="card" id="referee-resources">
                <div class="team-list-header">
                    <h2>Referee Resources</h2>
                </div>
            
                
                <div class="scrollable-container">
                <ul class="team-list">
                    <li class="team-list-item">
                        <a href="https://assets.the-afc.com/migration/a/f/afc-refereeing-guidelines-2020-21" target="_blank">
                            <i class="fa-solid fa-book"></i> AFC Refereeing Guidelines
                        </a>
                    </li>
                    <li class="team-list-item">
                        <a href="https://downloads.theifab.com/downloads/laws-of-the-game-2024-25?l=en" target="_blank">
                            <i class="fa-solid fa-scale-balanced"></i> IFAB Laws of the Game
                        </a>
                    </li>
                </ul>
                </div>
            </div>

            <?php
                require_once __DIR__ . '/../Include/db.php';
                //connect to db
                try {
                    $dbInstance = new Database();
                    $conn = $dbInstance->getConnection();
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }        
                
                $query = "
                    SELECT 
                        c.Name,
                        c.Issuer,
                        c.Notes,
                        rc.ExpiryDate
                    FROM Referee_Certificate rc
                    JOIN Certificate c ON rc.CertificateID = c.CertificateID
                    WHERE rc.RefereeID = 3
                "; 
            
            $result = $conn->query($query);
            ?>

            <div class="card" id="certificate">
                <div class="team-list-header">
                    <h2>Referee Certificates</h2>
                </div>

                <div class="scrollable-container">
                    <ul class="team-list">
                        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                            <li class="team-list-item">
                                <div class="team-list-item-text-container">
                                    <span><strong>Certificate:</strong> <?php echo htmlspecialchars($row['Name']); ?></span><br>
                                    <span><strong>Issuer:</strong> <?php echo htmlspecialchars($row['Issuer']); ?></span><br>
                                    <span><strong>Notes:</strong> <?php echo htmlspecialchars($row['Notes']); ?></span><br>
                                    <span><strong>Expiry Date:</strong> <?php echo date("F j, Y", strtotime($row['ExpiryDate'])); ?></span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <p>Please contact the Football Federation to renew your certification</p>
            </div>

                <?php
                require_once __DIR__ . '/../Include/db.php';
                try {
                    $dbInstance = new Database();
                    $conn = $dbInstance->getConnection();
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }
                            
                $query = "
                SELECT Team.TeamName, Premier_League_Standings.GamesPlayed, Premier_League_Standings.GoalDifference, Premier_League_Standings.Points
                FROM Premier_League_Standings
                JOIN Team ON Premier_League_Standings.TeamID = Team.TeamID
                ORDER BY Premier_League_Standings.Points DESC, Premier_League_Standings.GoalDifference DESC
                ";
                $result = $conn->query($query);
                $position = 1;
                ?>

                <div class="card" id="standings-prem">
                    <div class="team-list-header">
                        <h2>Premier League Standings</h2>
                    </div>
                    <div class="scrollable-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Team</th>
                                <th>PL</th>
                                <th>GD</th>
                                <th>PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
                                <tr>
                                    <td><?= $position++ ?></td>
                                    <td><?= htmlspecialchars($row['TeamName']) ?></td>
                                    <td><?= $row['GamesPlayed'] ?></td>
                                    <td><?= $row['GoalDifference'] ?></td>
                                    <td><?= $row['Points'] ?></td>
                                </tr>
                            <?php endwhile; ?>    
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php
                $dbInstance->closeConnection();
                ?>

                <?php
                require_once __DIR__ . '/../Include/db.php';
                //connect to db
                try {
                    $dbInstance = new Database();
                    $conn = $dbInstance->getConnection();
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }
                                
                $query = "
                SELECT Team.TeamName, La_Liga_Standings.GamesPlayed, La_Liga_Standings.GoalDifference, La_Liga_Standings.Points
                FROM La_Liga_Standings
                JOIN Team ON La_Liga_Standings.TeamID = Team.TeamID
                ORDER BY La_Liga_Standings.Points DESC, La_Liga_Standings.GoalDifference DESC
                ";
                $result = $conn->query($query);
                $position = 1;
                ?>

                <div class="card" id="standings-la-liga">
                    <div class="team-list-header">
                        <h2>La Liga Standings</h2>
                    </div>
                    <div class="scrollable-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Team</th>
                                <th>PL</th>
                                <th>GD</th>
                                <th>PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) : ?>
                                <tr>
                                    <td><?= $position++ ?></td>
                                    <td><?= htmlspecialchars($row['TeamName']) ?></td>
                                    <td><?= $row['GamesPlayed'] ?></td>
                                    <td><?= $row['GoalDifference'] ?></td>
                                    <td><?= $row['Points'] ?></td>
                                </tr>
                            <?php endwhile; ?>    
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php
                $dbInstance->closeConnection();
                ?>


                <?php
                require_once __DIR__ . '/../Include/db.php';
                //connect to db
                try {
                    $dbInstance = new Database();
                    $conn = $dbInstance->getConnection();
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }        
                
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
                WHERE lm.Status = 'Scheduled'
                ORDER BY lm.MatchDate ASC
            ";
            
            
            
            $result = $conn->query($query);
            ?>
            
            <div class="card" id="fixtures">
                <div class="team-list-header">
                    <h2>Your Upcoming Fixtures</h2>
                </div>
            
                <div class="scrollable-container">
                    <ul class="team-list">
                    <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <span><?php echo htmlspecialchars($row['HomeTeam'] . " vs " . $row['AwayTeam']); ?></span>
                                <span class="match-date-time">
                                    <?php 
                                        $datetime = date("F j, Y - H:i", strtotime($row['MatchDate']));
                                        echo htmlspecialchars($datetime); 
                                    ?>
                                </span>
                                <span class="fixture-venue">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?php echo htmlspecialchars($row['Stadium']); ?>
                                </span>
                            </div>
                        </li>
                    <?php endwhile; ?>

                    </ul>
                </div>
            </div>



            
            <?php
                $dbInstance->closeConnection();
            ?>


            <?php
                require_once __DIR__ . '/../Include/db.php';
                //connect to db
                try {
                    $dbInstance = new Database();
                    $conn = $dbInstance->getConnection();
                } catch (Exception $e) {
                    die("Error: " . $e->getMessage());
                }        
                
                $query = "
                    SELECT 
                        User.Firstname,
                        User.Surname,
                        Player.YellowCards,
                        Player.RedCards
                    FROM Player
                    INNER JOIN User ON Player.UserID = User.UserID
                    ORDER BY (Player.RedCards * 25 + Player.YellowCards * 10) DESC
                ";
                            
                
            
            $result = $conn->query($query);
            echo '<div class="card" id="booking-points">';
            echo '<div class="team-list-header"><h2>Booking Points</h2></div>';
            echo '<div class="scrollable-container">';
            echo '<table>';
            echo '<thead><tr>
                    <th>Player</th>
                    <th>Yellows</th>
                    <th>Reds</th>
                    <th>Total Booking Points</th>
                </tr></thead><tbody>';

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $fullname = $row['Firstname'] . ' ' . $row['Surname'];
                $yellows = $row['YellowCards'];
                $reds = $row['RedCards'];
                $points = ($reds * 25) + ($yellows * 10);

                echo "<tr>
                        <td>{$fullname}</td>
                        <td>{$yellows}</td>
                        <td>{$reds}</td>
                        <td>{$points}</td>
                    </tr>";
            }

            echo '</tbody></table>';
            echo '</div></div>';
            ?>
            <?php
                $dbInstance->closeConnection();
            ?>
        </div>
    </div>

        <!-- JAVA script to change colour of sidebar button referring to active page-->
        <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
        <script src="../sidebar.js"></script>
        <script>
            window.onload = preventPageRefresh;
        </script>
</html>