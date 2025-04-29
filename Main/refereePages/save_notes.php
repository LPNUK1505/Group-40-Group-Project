<?php
require_once __DIR__ . '/../Include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dbInstance = new Database();
        $conn = $dbInstance->getConnection();

        $refereeId = intval($_POST['referee_id']);
        $leagueMatchId = intval($_POST['league_match_id']);
        $rating = intval($_POST['rating']);
        $notes = htmlspecialchars(trim($_POST['notes']));

        if (empty($refereeId) || empty($leagueMatchId) || empty($rating) || empty($notes)) {
            throw new Exception("Missing required fields.");
        }

        // Check if a report already exists
        $checkQuery = $conn->prepare('
            SELECT COUNT(*) AS count 
            FROM Referee_Report 
            WHERE RefereeID = :referee_id AND LeagueMatchID = :league_match_id
        ');
        $checkQuery->bindValue(':referee_id', $refereeId, SQLITE3_INTEGER);
        $checkQuery->bindValue(':league_match_id', $leagueMatchId, SQLITE3_INTEGER);

        $result = $checkQuery->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row['count'] > 0) {
            // Exists -> Update
            $updateQuery = $conn->prepare('
                UPDATE Referee_Report 
                SET Rating = :rating, Notes = :notes 
                WHERE RefereeID = :referee_id AND LeagueMatchID = :league_match_id
            ');
            $updateQuery->bindValue(':rating', $rating, SQLITE3_INTEGER);
            $updateQuery->bindValue(':notes', $notes, SQLITE3_TEXT);
            $updateQuery->bindValue(':referee_id', $refereeId, SQLITE3_INTEGER);
            $updateQuery->bindValue(':league_match_id', $leagueMatchId, SQLITE3_INTEGER);

            $success = $updateQuery->execute();
            $message = "Updated existing report!";
        } else {
            // Doesn't exist -> Insert
            $insertQuery = $conn->prepare('
                INSERT INTO Referee_Report (RefereeID, LeagueMatchID, Rating, Notes) 
                VALUES (:referee_id, :league_match_id, :rating, :notes)
            ');
            $insertQuery->bindValue(':referee_id', $refereeId, SQLITE3_INTEGER);
            $insertQuery->bindValue(':league_match_id', $leagueMatchId, SQLITE3_INTEGER);
            $insertQuery->bindValue(':rating', $rating, SQLITE3_INTEGER);
            $insertQuery->bindValue(':notes', $notes, SQLITE3_TEXT);

            $success = $insertQuery->execute();
            $message = "Inserted new report!";
        }

        if ($success) {
            header("Location: submission_results.php?message=" . urlencode($message));
            exit;
        } else {
            throw new Exception("Database operation failed: " . $conn->lastErrorMsg());
        }

    } catch (Exception $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    } finally {
        if (isset($dbInstance)) {
            $dbInstance->closeConnection();
        }
    }
} else {
    echo "Invalid request.";
}
?>
