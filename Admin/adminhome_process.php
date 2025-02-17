<?php
session_start();
require_once 'connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id']) || !$_SESSION['is_admin']) {
    die(json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_stats') {
    try {
        $stats = [];
        
        // Get total candidates
        $candidateQuery = "SELECT COUNT(*) as total FROM candidates";
        $result = $conn->query($candidateQuery);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_candidates'] = $row['total'];
        } else {
            throw new Exception("Error fetching candidates: " . $conn->error);
        }
        
        // Get total registered voters
        $voterQuery = "SELECT COUNT(*) as total FROM login";
        $result = $conn->query($voterQuery);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['registered_voters'] = $row['total'];
        } else {
            throw new Exception("Error fetching voters: " . $conn->error);
        }
        
        // Get total votes cast (unique voters)
        $votesQuery = "SELECT COUNT(DISTINCT user_id) as total FROM votes";
        $result = $conn->query($votesQuery);
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['votes_cast'] = $row['total'];
        } else {
            throw new Exception("Error fetching votes: " . $conn->error);
        }
        
        // Calculate voter turnout
        $stats['voter_turnout'] = $stats['registered_voters'] > 0 
            ? round(($stats['votes_cast'] / $stats['registered_voters']) * 100, 1)
            : 0;
            
        // Get recent activities
        $activitiesQuery = "
            (SELECT 'vote' as type, vote_time as time, 'fa-vote-yea' as icon,
                    CONCAT('New vote cast for ', position) as message
             FROM votes 
             ORDER BY vote_time DESC 
             LIMIT 3)
            UNION ALL
            (SELECT 'registration' as type, created_at as time, 'fa-user-plus' as icon,
                    CONCAT('New voter registered: ', name) as message
             FROM login 
             ORDER BY created_at DESC 
             LIMIT 3)
            ORDER BY time DESC 
            LIMIT 5";
        
        $result = $conn->query($activitiesQuery);
        $activities = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $timestamp = strtotime($row['time']);
                $row['time'] = date('M j, Y g:i A', $timestamp);
                $activities[] = $row;
            }
        } else {
            throw new Exception("Error fetching activities: " . $conn->error);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'activities' => $activities
        ]);
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

// Close the connection
$conn->close();
?>