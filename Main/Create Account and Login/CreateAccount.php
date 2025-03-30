<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
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
    <h1>Create Account</h1>
    <form action="../Team Manager/TeamOverview.html" id="create-account-form">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <select name="role">
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="player">Player</option>
            <option value="team_manager">Team Manager</option>
            <option value="referee">Referee</option>
        </select>
        <button type="submit">Create Account</button>
    </form>
</div>

</body>
</html>
