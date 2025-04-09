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

            #standings { 
                grid-column: span 3;
                grid-row: span 4;
            }

            #friendly {
                grid-column: span 3;
                grid-row: span 2;
            }

            #recent-matches {
                grid-column: span 3;
                grid-row: span 2;
            }

            #squad-summary {
                grid-column: span 3;
                grid-row: span 2;
            }

            #fixtures {
                grid-column: 4 / 13;
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
                        <?php
                        $db = new SQLITE3(filename: 'db.php' );
                        $select_query = "SELECT * FROM League_Match";
                        $result = $db- >query(query: $select_query);
                        echo "<ul class="fixture-list">";
                        <a href="UpcomingMatches.html" class="fixture-list">
                        echo "<li class="fixture-list-item">";
                        

                    

                        




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

    <!-- JAVA script to change colour of sidebar button referring to active page-->
    <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
    <script src="../sidebar.js"></script>
    <script>
        window.onload = preventPageRefresh;
    </script>
</html>