<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://kit.fontawesome.com/d15bb23cbb.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<div class="header"></div>
<div class="sidebar">
    <img src="../GoIkonLogoFinal.png" alt="Goikon Logo" class="goikon-logo">
    <div class="sidebar-separator"></div>
</div>
<div class="footer">
    <a href="AboutUs.html" class="stayOnPageLink">About Us</a>
    <a href="ContactUs.html">Contact Us</a>
</div>

<div class="main-content">
    <h2>Login</h2>
    <form action="../Team Manager/TeamOverview.html" id="login-form">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <?php if (isset($_GET['error'])) echo "<p style='color:red;'>{$_GET['error']}</p>"; ?>
        <button type="submit">Login</button>
        <p><a href="CreateAccount.php" class="create-account">Create Account</a></p>

    </form>
</div>

</body>
</html>
