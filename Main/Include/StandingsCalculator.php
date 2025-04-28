<?php
declare(strict_types=1);

/**
 * Calculates league standings from match results
 * @param SQLite3 $db Active database connection
 * @param int $leagueId The league ID to calculate standings for
 * @return array Sorted array of team standings with all stats
 */
function calculateLeagueStandings(SQLite3 $db, int $leagueId): array {
    // Initialize teams array
    $teams = [];
    
    // 1. Get all teams in this league
    $stmt = $db->prepare("
        SELECT TeamID, TeamName 
        FROM Team 
        WHERE LeagueID = :leagueId
    ");
    $stmt->bindValue(':leagueId', $leagueId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    while ($team = $result->fetchArray(SQLITE3_ASSOC)) {
        $teams[$team['TeamID']] = [
            'TeamName' => $team['TeamName'],
            'Played' => 0,
            'Wins' => 0,
            'Draws' => 0,
            'Losses' => 0,
            'GoalsFor' => 0,
            'GoalsAgainst' => 0,
            'GoalDifference' => 0,
            'Points' => 0
        ];
    }

    // 2. Process all completed league matches
    $stmt = $db->prepare("
        SELECT 
            lm.LeagueMatchID,
            lm.HomeTeamID,
            lm.AwayTeamID,
            (SELECT COUNT(*) FROM Match_Report_Log 
             WHERE LeagueMatchID = lm.LeagueMatchID AND TeamID = lm.HomeTeamID AND Event = 'Goal') AS HomeGoals,
            (SELECT COUNT(*) FROM Match_Report_Log 
             WHERE LeagueMatchID = lm.LeagueMatchID AND TeamID = lm.AwayTeamID AND Event = 'Goal') AS AwayGoals
        FROM League_Match lm
        WHERE lm.LeagueID = :leagueId 
        AND lm.Status = 'Completed'
    ");
    $stmt->bindValue(':leagueId', $leagueId, SQLITE3_INTEGER);
    $result = $stmt->execute();

    while ($match = $result->fetchArray(SQLITE3_ASSOC)) {
        $homeId = $match['HomeTeamID'];
        $awayId = $match['AwayTeamID'];
        $homeGoals = (int)$match['HomeGoals'];
        $awayGoals = (int)$match['AwayGoals'];
        
        // Update match counts
        $teams[$homeId]['Played']++;
        $teams[$awayId]['Played']++;
        
        // Update goals
        $teams[$homeId]['GoalsFor'] += $homeGoals;
        $teams[$homeId]['GoalsAgainst'] += $awayGoals;
        $teams[$awayId]['GoalsFor'] += $awayGoals;
        $teams[$awayId]['GoalsAgainst'] += $homeGoals;
        
        // Update goal difference
        $teams[$homeId]['GoalDifference'] = $teams[$homeId]['GoalsFor'] - $teams[$homeId]['GoalsAgainst'];
        $teams[$awayId]['GoalDifference'] = $teams[$awayId]['GoalsFor'] - $teams[$awayId]['GoalsAgainst'];
        
        // Calculate points
        if ($homeGoals > $awayGoals) {
            $teams[$homeId]['Wins']++;
            $teams[$homeId]['Points'] += 3;
            $teams[$awayId]['Losses']++;
        } elseif ($awayGoals > $homeGoals) {
            $teams[$awayId]['Wins']++;
            $teams[$awayId]['Points'] += 3;
            $teams[$homeId]['Losses']++;
        } else {
            $teams[$homeId]['Draws']++;
            $teams[$awayId]['Draws']++;
            $teams[$homeId]['Points'] += 1;
            $teams[$awayId]['Points'] += 1;
        }
    }

    // 3. Convert to array and sort
    $standings = array_values($teams);
    usort($standings, function($a, $b) {
        // Sort by Points DESC, GD DESC, GoalsFor DESC
        if ($b['Points'] !== $a['Points']) {
            return $b['Points'] <=> $a['Points'];
        }
        if ($b['GoalDifference'] !== $a['GoalDifference']) {
            return $b['GoalDifference'] <=> $a['GoalDifference'];
        }
        return $b['GoalsFor'] <=> $a['GoalsFor'];
    });

    return $standings;
}

function getRecentMatchesForTeam($db, $teamName) {
    $stmt = $db->prepare("
        SELECT * FROM Matches 
        WHERE HomeTeam = :team OR AwayTeam = :team 
        ORDER BY MatchDate DESC
        LIMIT 10
    ");
    $stmt->bindValue(':team', $teamName, SQLITE3_TEXT);
    $result = $stmt->execute();

    $matches = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $isHome = $row['HomeTeam'] == $teamName;
        $row['TeamGoals'] = $isHome ? $row['HomeGoals'] : $row['AwayGoals'];
        $row['OpponentGoals'] = $isHome ? $row['AwayGoals'] : $row['HomeGoals'];
        $matches[] = $row;
    }

    return $matches;
}

function getUpcomingMatchesForTeam($db, $teamName) {
    $stmt = $db->prepare("
        SELECT * FROM Matches 
        WHERE (HomeTeam = :team OR AwayTeam = :team)
          AND MatchDate > datetime('now')
        ORDER BY MatchDate ASC
        LIMIT 10
    ");
    $stmt->bindValue(':team', $teamName, SQLITE3_TEXT);
    $result = $stmt->execute();

    $matches = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $matches[] = $row;
    }

    return $matches;
}
