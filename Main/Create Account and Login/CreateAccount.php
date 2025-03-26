
<!-- ctrl+a then paste into VSCode -->




<!DOCTYPE html>
<html>
  
   <link rel="stylesheet" href="../styles.css">
    <title>Create Account</title>
   </head>
   <?php
        include("../Template.html");
    ?>
   <body>
   <div class="main-content">
   <h2>Create Account</h2>
    <form method="post" action="CreateAccount.php" id="CreateAccount">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="text" name="Firstname" placeholder="Firstname" required><br>
        <input type="text" name="Lastname" placeholder="Lastname" required><br>
        <input type="email" name="Email Address" placeholder="Email Address" required><br>
        
    <select id="role" name="role">
    <option value="" disabled selected>Select Role</option>
        <option value="admin">Admin</option>
        <option value="player">Player</option>
        <option value="team_manager">Team Manager</option>
        <option value="referee">Referee</option>
    </select>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <button type="submit">Create Account</button>
    </form>
    
   </div>
</body>
</html>

<!-- STYLING FOR FONTAWESOME ICONS -->
<!-- https://docs.fontawesome.com/web/style/styling -->


<!-- OTHER COOL ICONS -->
<!-- <i class="fa-solid fa-house-chimney"></i> HOME ICON
 <i class="fa-solid fa-hand-middle-finger"></i>
<i class="fa-solid fa-futbol"></i> FOOTBALL
 <i class="fa-solid fa-comment-dots"></i> CONTACT/ SUPPORT BUBBLE
 <i class="fa-solid fa-chart-line"></i> CHART/ STONKS
 -->

