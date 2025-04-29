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
                <a href="TeamOverview.html">
                    <i class="fa-solid fa-people-group"></i>Team Overview
                </a>
            </div>
            <div class="sidebar-button">
                <a href="RosterManagement.php">
                    <i class="fa-solid fa-user-plus"></i>Roster Management
                </a>
            </div>
            <div class="sidebar-button">
                <a href="MatchPreperation.html">
                    <i class="fa-solid fa-square-check"></i>Match Preperation
                </a>
            </div>
            <div class="sidebar-button">
                <a href="UpcomingMatches.html">
                    <i class="fa-solid fa-calendar"></i>Upcoming Matches
                </a>
            </div>
            <div class="sidebar-button">
                <a href="PlayerStats.php" class="stayOnPageLink">
                    <i class="fa-solid fa-chart-simple"></i>Player Stats
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
            <h1>Player Stats</h1>
            <table border="1" class="player-stats-table">
    <tr>
        <th>Player Name</th>
        <th>Games Played</th>
        <th>Goals</th>
        <th>Assists</th>
        <th>Yellow Cards</th>
        <th>Red Cards</th>
    </tr>
    <tr>
        <td>John Smith</td>
        <td>10</td>
        <td>5</td>
        <td>3</td>
        <td>2</td>
        <td>0</td>
    </tr>
    <tr>
        <td>Michael Johnson</td>
        <td>12</td>
        <td>8</td>
        <td>4</td>
        <td>1</td>
        <td>0</td>
    </tr>
    <tr>
        <td>David Williams</td>
        <td>9</td>
        <td>3</td>
        <td>6</td>
        <td>3</td>
        <td>1</td>
    </tr>
    <tr>
        <td>Amad Diallo</td>
        <td>15</td>
        <td>15</td>
        <td>4</td>
        <td>2</td>
        <td>0</td>
    </tr>
    <tr>
        <td>Robert Davis</td>
        <td>11</td>
        <td>6</td>
        <td>5</td>
        <td>2</td>
        <td>1</td>
    </tr>
    <tr>
        <td>Jayden Colwil</td>
        <td>14</td>
        <td>9</td>
        <td>8</td>
        <td>1</td>
        <td>0</td>
    </tr>
    <tr>
        <td>Chris Anderson</td>
        <td>13</td>
        <td>7</td>
        <td>4</td>
        <td>2</td>
        <td>1</td>
    </tr>
    <tr>
        <td>Emi Martinez</td>
        <td>10</td>
        <td>0</td>
        <td>2</td>
        <td>2</td>
        <td>0</td>
    </tr>
</table>

<div class="player-stats-navigation">
    <button class="prev-page">&laquo; Prev</button>
    <span>Page 1</span>
    <button class="next-page">Next &raquo;</button>
</div>

        </div>
        <!-- JAVA script to change colour of sidebar button referring to active page-->
        <!-- REQUIRES a class to be added to the active button-CHECK SettingsPersonal.html for an example-->
        <script src="../sidebar.js"></script>
        <script>
            window.onload = preventPageRefresh;
        </script>
    </html>