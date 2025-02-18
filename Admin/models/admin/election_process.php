<?php
date_default_timezone_set('Africa/Nairobi'); // or your correct time zone

// Include the connection file (adjust the path if needed)
require_once '../../controllers/connection.php';

// Use $conn as your database connection or alias it to $db
$db = $conn;

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'start':
            startElection();
            break;
        case 'end':
            endElection();
            break;
        case 'update_settings':
            updateSettings();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_status':
            getElectionStatus();
            break;
        case 'get_countdown':
            getCountdown();
            break;
    }
}

// Function to start election
function startElection() {
    global $db;
    
    $now = new DateTime();
    $settingsResult = $db->query("SELECT * FROM election_settings ORDER BY id DESC LIMIT 1");
    $settings = $settingsResult ? $settingsResult->fetch_assoc() : null;
    
    if (!$settings) {
        echo json_encode(['success' => false, 'message' => 'Please set election times first']);
        return;
    }
    
    $startTime = new DateTime($settings['election_start']);
    if ($startTime > $now) {
        echo json_encode(['success' => false, 'message' => 'Cannot start election before scheduled time']);
        return;
    }
    
    // Update election status in your settings table
    $db->query("UPDATE election_settings SET status = 'In Progress' WHERE id = {$settings['id']}");
    
    echo json_encode(['success' => true]);
}

// Function to end election
function endElection() {
    global $db;
    
    // Update election status
    $db->query("UPDATE election_settings SET status = 'Ended' WHERE id = (SELECT id FROM (SELECT id FROM election_settings ORDER BY id DESC LIMIT 1) AS sub)");
    
    echo json_encode(['success' => true]);
}

// Function to update settings
function updateSettings() {
    global $db;
    
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    
    $stmt = $db->prepare("INSERT INTO election_settings (election_start, election_end) VALUES (?, ?)");
    $stmt->bind_param("ss", $startTime, $endTime);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update settings']);
    }
}

// Function to get election status
function getElectionStatus() {
    global $db;
    
    // Get total voters
    $totalVotersResult = $db->query("SELECT COUNT(*) as total FROM login");
    $totalVoters = $totalVotersResult ? $totalVotersResult->fetch_assoc()['total'] : 0;
    
    // Get votes cast
    $votesCastResult = $db->query("SELECT COUNT(DISTINCT voter_id) as total FROM votes");
    $votesCast = $votesCastResult ? $votesCastResult->fetch_assoc()['total'] : 0;
    
    // Calculate turnout
    $turnout = $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 2) : 0;
    
    // Get latest election settings
    $settingsResult = $db->query("SELECT * FROM election_settings ORDER BY id DESC LIMIT 1");
    $settings = $settingsResult ? $settingsResult->fetch_assoc() : null;
    
    if (!$settings) {
        echo json_encode(['success' => false, 'message' => 'Election settings not found']);
        return;
    }
    
    // Determine current status
    $now = new DateTime();
    $startTime = new DateTime($settings['election_start']);
    $endTime = new DateTime($settings['election_end']);
    
    if ($now < $startTime) {
        $status = "Not Started";
    } elseif ($now > $endTime) {
        $status = "Ended";
    } else {
        $status = "In Progress";
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_voters' => $totalVoters,
            'turnout' => $turnout,
            'status' => $status,
            'start_time' => $settings['election_start'],
            'end_time' => $settings['election_end']
        ]
    ]);
}

// Function to get countdown
function getCountdown() {
    global $db;
    
    $settingsResult = $db->query("SELECT * FROM election_settings ORDER BY id DESC LIMIT 1");
    $settings = $settingsResult ? $settingsResult->fetch_assoc() : null;
    
    if (!$settings) {
        echo json_encode(['success' => false, 'message' => 'Election settings not found']);
        return;
    }
    
    $now = new DateTime();
    $startTime = new DateTime($settings['election_start']);
    $endTime = new DateTime($settings['election_end']);
    
    if ($now < $startTime) {
        $interval = $now->diff($startTime);
        $status = "Until Start";
    } elseif ($now < $endTime) {
        $interval = $now->diff($endTime);
        $status = "Until End";
    } else {
        echo json_encode([
            'success' => true,
            'time_remaining' => "00:00:00",
            'status' => "Election Ended"
        ]);
        return;
    }
    
    $timeRemaining = sprintf(
        "%02d:%02d:%02d",
        $interval->h + ($interval->days * 24),
        $interval->i,
        $interval->s
    );
    
    echo json_encode([
        'success' => true,
        'time_remaining' => $timeRemaining,
        'status' => $status
    ]);
}
?>
