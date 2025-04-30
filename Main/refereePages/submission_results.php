<?php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Submission completed!';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submission Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .status-container {
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .status-container h1 {
            color: #28a745;
            margin-bottom: 20px;
        }

        .status-container a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .status-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <h1><?php echo $message; ?></h1>
        <p><a href="MatchResults.php">Back to Matches</a></p>
    </div>
</body>
</html>
