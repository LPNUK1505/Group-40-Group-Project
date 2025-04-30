<?php
require_once __DIR__ . '/../Include/db.php';

try {
    $dbInstance = new Database();
    $conn = $dbInstance->getConnection();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$refereeID = intval($_POST['referee_id']);
$leagueMatchID = intval($_POST['league_match_id']);
$teamID = intval($_POST['team_id']);
$playerID = intval($_POST['player_id']);
$playerNumber = intval($_POST['player_number']);
$event = trim($_POST['event']);
$time = intval($_POST['time']);
$reason = trim($_POST['reason']);

if (!in_array($event, ['Red Card', 'Yellow Card']) || strlen($reason) > 30) {
    die("Invalid event or reason too long.");
}

$stmt = $conn->prepare("
    INSERT INTO Misconduct_Log (
        RefereeID, LeagueMatchID, TeamID,
        PlayerID, PlayerNumber, Event, Time, Reason
    ) VALUES (
        :refereeID, :leagueMatchID, :teamID,
        :playerID, :playerNumber, :event, :time, :reason
    )
");

$stmt->bindValue(':refereeID', $refereeID, SQLITE3_INTEGER);
$stmt->bindValue(':leagueMatchID', $leagueMatchID, SQLITE3_INTEGER);
$stmt->bindValue(':teamID', $teamID, SQLITE3_INTEGER);
$stmt->bindValue(':playerID', $playerID, SQLITE3_INTEGER);
$stmt->bindValue(':playerNumber', $playerNumber, SQLITE3_INTEGER);
$stmt->bindValue(':event', $event, SQLITE3_TEXT);
$stmt->bindValue(':time', $time, SQLITE3_INTEGER);
$stmt->bindValue(':reason', $reason, SQLITE3_TEXT);

if ($stmt->execute()) {
    header("Location: MisconductLog.php?success=1");
    exit;
} else {
    die("Failed to save misconduct.");
}
?>
