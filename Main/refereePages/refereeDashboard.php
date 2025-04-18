<!DOCTYPE html>
<html>
   <!-- JAVA line for 'fontawesome' icons -->
   <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="../styles.css">


   <!-- Creates the Header at the top -->
   <div class="header">
       <div class="header-menu-icon">
           <i class="fa-solid fa-bars"></i>
       </div>

       <!-- Creates icons for the right hand side of the header -->
       <div class="header-right-icon">
           <i class="fa-solid fa-gear"></i>
           <i class="fa-solid fa-user-large"></i>
       </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
       <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">

       <!-- Creates buttons in the Sidebar -->
       <a href="assignedMatches.html">Assigned Matches</a>
       <a href="misconductLog.html">Misconduct Log</a>
       <a href="matchResults.html">Match Results</a>
       <a href="matchStatistics.html">Match Statistics</a>
       <a href="matchPolicies.html">Match Policies</a>
       <a href="refereeGuidelines.html">Referee Guidelines</a>
   </div>


   <!-- Creates the Footer at the bottom -->
   <div class="footer">

       <!-- Creates buttons in the footer -->
       <a href="#Option 1">Option 1</a>
       <a href="#Option 2">Option 2</a>
       <a href="#Option 3">Option 3</a>
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


    
   </div>
</html>