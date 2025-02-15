<?php
// Include your database connection file. This file should create a $pdo instance.
include 'connection.php';

header('Content-Type: application/json');

try {
    // Query to fetch all announcements
    $stmt = $pdo->prepare("SELECT id, title, content, status, priority, created_by, created_at, updated_at, attachments FROM announcements ORDER BY created_at DESC");
    $stmt->execute();
    
    // Fetch all announcements as an associative array
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the data as JSON
    echo json_encode($announcements);
} catch (Exception $e) {
    // In case of error, return an empty JSON array or a proper error message
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
