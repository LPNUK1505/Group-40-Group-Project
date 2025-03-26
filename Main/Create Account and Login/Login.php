


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
    <h2>Login</h2>
    <form method="post" action="Login.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
       
        <button type="submit">Login</button> 
        <p><a href="CreateAccount.php" class="create-account">Create Account</a></p>

    </form>
    </div>
</html>