<?php
include_once __DIR__ . '/../Include/db.php'; // Path to db.php (which includes the Database class)

// Instantiate the Database class to get the connection
try {
    $dbInstance = new Database();  // Instantiate the Database class
    $conn = $dbInstance->getConnection(); // Get the connection
} catch (Exception $e) {
    die("Error: " . $e->getMessage());  // If there's an error, display a message and stop execution
}
?>


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
            <a href="TeamOverview.html" class="stayOnPageLink">
                <i class="fa-solid fa-people-group"></i>Player Overview
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

    <!-- Creates Main Content area -->
    <div class="main-content">
    <style>
    /* Update your dashboard grid layout */
    .dashboard {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        grid-template-rows: auto auto 1fr;
        gap: 20px;
        height: calc(100vh - 180px);
    }

    /* Adjust card sizes */
    #standings { 
        grid-column: span 3; 
        grid-row: span 2;
    }
    
    #recent-matches { 
        grid-column: span 3;
        grid-row: span 1;
    }
    
    #player-stats {
        grid-column: span 3;
        grid-row: span 1;
        min-height: 300px; /* Added minimum height */
    }
    
    #team-roster {
        grid-column: span 3;
        grid-row: span 1;
        min-height: 300px; /* Added minimum height */
    }
    
    #fixtures {
        grid-column: 4 / 13;
        grid-row: 1 / 3;
    }

    /* Make cards expand to fill space */
    .card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Ensure scrollable containers have proper height */
    .scrollable-container {
        flex-grow: 1;
        overflow-y: auto;
        min-height: 200px; /* Minimum height for content */
    }

    /* Adjust table sizing */
    .stats-table, .roster-table {
        width: 100%;
        table-layout: fixed; /* Ensures consistent column widths */
    }
    
    .stats-table th, 
    .stats-table td,
    .roster-table th,
    .roster-table td {
        padding: 12px 8px;
        text-align: left;
    }
</style>

        <h1> Team Overview </h1>
        <div class="dashboard">
            <div class="card"id="fixtures">
                <div class="team-list-header">
                    <h2>Upcoming Fixtures</h2>
                </div>

                <div class="scrollable-container">
                    <ul class="fixture-list">
                        <a href="UpcomingMatches.html" class="fixture-list">
                            <li class="fixture-list-item">
                                    <div class="fixture-list-item-text-container">
                                        <span>Charlie City vs Hotel FC</span>
                                        <span class="match-date-time">April 9, 2025 - 17:00</span>
                                        <div class="fixture-list-item-additional">
                                            <span class="fixture-venue"><i class="fa-solid fa-location-dot"></i> Wembley Stadium</span>
                                            <span class="fixture-competition"><i class="fa-solid fa-trophy"></i> Friendly</span>
                                        </div>
                                    </div>
                            </li>

                        




                            <li class="fixture-list-item">
                                <div class="fixture-list-item-text-container">
                                    <span>Charlie City vs Hotel FC</span>
                                    <span class="match-date-time">April 13, 2025 - 15:30</span>
                                    <div class="fixture-list-item-additional">
                                        <span class="fixture-venue"><i class="fa-solid fa-location-dot"></i> National Stadium</span>
                                        <span class="fixture-competition"><i class="fa-solid fa-trophy"></i> League</span>
                                    </div>
                                </div>
                            </li>

                            <li class="fixture-list-item">
                                <div class="fixture-list-item-text-container">
                                    <span>Charlie City vs Hotel FC</span>
                                    <span class="match-date-time">April 26, 2025 - 15:00</span>
                                    <div class="fixture-list-item-additional">
                                        <span class="fixture-venue"><i class="fa-solid fa-location-dot"></i> Reebok Stadium</span>
                                        <span class="fixture-competition"><i class="fa-solid fa-trophy"></i> League</span>
                                    </div>
                                </div>
                            </li>
                        </a>
                    </ul>
                </div>
            </div>

            <div class="card" id="standings">
                <div class="team-list-header">
                    <h2>League Standings</h2>
                </div>

                <div class="scrollable-container">
                    <table>
                        <tr><th>Pos</th><th>Team</th><th>Pl</th><th>GD</th><th>Pts</th></tr>
                        <tr><td>1</td><td>Alpha FC</td><td>10</td><td>+15</td><td>30</td></tr>
                        <tr><td>2</td><td>Bravo United</td><td>10</td><td>+12</td><td>28</td></tr>
                        <tr><td>3</td><td>Charlie City</td><td>10</td><td>+9</td><td>24</td></tr>
                        <tr><td>4</td><td>Delta Rovers</td><td>10</td><td>+8</td><td>22</td></tr>
                        <tr><td>5</td><td>Echo Town</td><td>10</td><td>+7</td><td>20</td></tr>
                        <tr><td>6</td><td>Foxtrot United</td><td>10</td><td>+5</td><td>18</td></tr>
                        <tr><td>7</td><td>Golf Rangers</td><td>10</td><td>+3</td><td>16</td></tr>
                        <tr><td>8</td><td>Hotel FC</td><td>10</td><td>+1</td><td>14</td></tr>
                        <tr><td>9</td><td>India Tigers</td><td>10</td><td>-1</td><td>12</td></tr>
                        <tr><td>10</td><td>Juliet Eagles</td><td>10</td><td>-3</td><td>10</td></tr>
                        <tr><td>11</td><td>Kilo Wanderers</td><td>10</td><td>-5</td><td>8</td></tr>
                        <tr><td>12</td><td>Lima United</td><td>10</td><td>-7</td><td>6</td></tr>
                        <tr><td>13</td><td>Mike City</td><td>10</td><td>-9</td><td>4</td></tr>
                        <tr><td>14</td><td>November FC</td><td>10</td><td>-12</td><td>2</td></tr>
                        <tr><td>15</td><td>Oscar Knights</td><td>10</td><td>-15</td><td>0</td></tr>
                    </table>
                </div>
            </div>

           

            <div class="card" id="recent-matches">
                <div class="team-list-header">
                    <h2>Recent Matches</h2>
                </div>

                <div class="scrollable-container">
                    <ul class="team-list">
                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <div class="match-info">
                                    <span class="match-details">vs Golf Rangers</span>
                                    <span class="match-score win">3 - 1</span>
                                </div>
                                <span class="match-date-time">March 29, 2025</span>
                            </div>
                        </li>

                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <div class="match-info">
                                    <span class="match-details">vs Echo Town</span>
                                    <span class="match-score draw">2 - 2</span>
                                </div>
                                <span class="match-date-time">March 26, 2025</span>
                            </div>
                        </li>

                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <div class="match-info">
                                    <span class="match-details">vs Alpha FC</span>
                                    <span class="match-score win">1 - 0</span>
                                </div>
                                <span class="match-date-time">March 22, 2025</span>
                            </div>
                        </li>

                        <li class="team-list-item">
                            <div class="team-list-item-text-container">
                                <div class="match-info">
                                    <span class="match-details">vs Mike City</span>
                                    <span class="match-score lose">0 - 2</span>
                                </div>
                                <span class="match-date-time">March 19, 2025</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card" id="squad-summary">
                <div class="team-list-header">
                    <h2>My Stats</h2>
                </div>

                <div class="scrollable-container">
                            <table class="squad-summary-table">
                                <tr><th>Appearances</th><th>Goals</th><th>Assists</th><th>Yellow Cards</th><th>Red Cards</th></tr>
                                <tr><td>25</td><td>13</td><td>7</td><td>CB</td><td>9</td><td>1</td></tr>
                                
                            </table>
                </div>
            </div>

            <div class="card" id="squad-summary">
                <div class="team-list-header">
                    <h2>My Team</h2>
                </div>

                <div class="scrollable-container">
                            <table class="squad-summary-table">
                                <tr><th>Player</th><th>Goals</th><th>Assists</th><th>Yellow Cards</th><th>Red Cards</th></tr>
                                <tr>
        <td>John Smith</td>
        <td>10</td>
        <td>5</td>
        <td>3</td>
        <td>2</td>
      
    </tr>
    <tr>
        <td>Michael Johnson</td>
        <td>12</td>
        <td>8</td>
        <td>4</td>
        <td>1</td>
        
    </tr>
    <tr>
        <td>David Williams</td>
        <td>9</td>
        <td>3</td>
        <td>6</td>
        <td>3</td>
        
    </tr>
    <tr>
        <td>Amad Diallo</td>
        <td>15</td>
        <td>15</td>
        <td>4</td>
        <td>2</td>
       
    </tr>
    <tr>
        <td>Robert Davis</td>
        <td>11</td>
        <td>6</td>
        <td>5</td>
        <td>2</td>
       
    </tr>
    <tr>
        <td>Jayden Colwil</td>
        <td>14</td>
        <td>9</td>
        <td>8</td>
        <td>1</td>
        
    </tr>
    <tr>
        <td>Chris Anderson</td>
        <td>13</td>
        <td>7</td>
        <td>4</td>
        <td>2</td>
        
    </tr>
    <tr>
        <td>Emi Martinez</td>
        <td>10</td>
        <td>0</td>
        <td>2</td>
        <td>2</td>
       
    </tr>
                                
                            </table>
                </div>
            </div>
        </div>
    </div>
<!-- My Stats Card (larger version) -->
<div class="card" id="player-stats">
    <h2>My Stats</h2>
    <div class="scrollable-container">
        <table class="stats-table">
            <tr>
                <th>Appearances</th>
                <td><?= $player['Appearances'] ?></td>
            </tr>
            <tr>
                <th>Goals</th>
                <td><?= $player['Goals'] ?></td>
            </tr>
            <tr>
                <th>Assists</th>
                <td><?= $player['Assists'] ?></td>
            </tr>
            <tr>
                <th>Yellow Cards</th>
                <td><?= $player['YellowCards'] ?></td>
            </tr>
            <tr>
                <th>Red Cards</th>
                <td><?= $player['RedCards'] ?></td>
            </tr>
            <!-- Add more rows if needed -->
        </table>
    </div>
</div>

<!-- Team Roster Card (larger version) -->
<div class="card" id="team-roster">
    <h2>Team Roster</h2>
    <div class="scrollable-container">
        <table class="roster-table">
            <thead>
                <tr>
                    <th style="width: 60%">Player</th>
                    <th style="width: 20%">Goals</th>
                    <th style="width: 20%">Assists</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($teamPlayers, 0, 10) as $tp): ?>
                <tr <?= $tp['PlayerID'] == $playerId ? 'class="highlight"' : '' ?>>
                    <td><?= htmlspecialchars($tp['PlayerName']) ?></td>
                    <td><?= $tp['Goals'] ?></td>
                    <td><?= $tp['Assists'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>