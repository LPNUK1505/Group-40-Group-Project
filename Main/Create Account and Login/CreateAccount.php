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
            <a href="SettingsPersonal.html">
                <i class="fa-solid fa-gear"></i> 
            </a>
            <i class="fa-solid fa-user-large"></i>
       </div>
   </div>
   

   <!-- Creates the Sidebar on the left hand side -->
   <div class="sidebar">
       <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class = "goikon-logo">

       <!-- Creates buttons in the Sidebar -->
       <div class="sidebar-button">
        <a href="#Option 1">
            <i class="fa-solid fa-gear"></i>Option 1
        </a>
        </div>
        <div class="sidebar-button">
            <a href="#Option 2">
                <i class="fa-solid fa-gear"></i>Option 2
            </a>
        </div>
   </div>


   <!-- Creates the Footer at the bottom -->
   <div class="footer">

       <!-- Creates buttons in the footer -->
       <a href="AboutUs.html" class="stayOnPageLink">About Us</a>
       <a href="ContactUs.html">Contact Us</a>
   </div>
   
   <div class="main-content">
    <h2>Create Account</h2>
    <form method="post" action="CreateAccount.php" id="CreateAccount">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="Firstname" placeholder="Firstname" required>
        <input type="text" name="Lastname" placeholder="Lastname" required>
        <input type="email" name="Email Address" placeholder="Email Address" required>
        <select id="role" name="role">
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="player">Player</option>
            <option value="team_manager">Team Manager</option>
            <option value="referee">Referee</option>
        </select>
        <button type="submit">Create Account</button>
    </form>
</div>
</html>