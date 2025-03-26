<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../styles.css">
    <title></title>
</head>
<?php
        include("../Template.html");
    ?>
<body>
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
   
</body>
</html>