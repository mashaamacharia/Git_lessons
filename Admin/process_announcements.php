<?php
session_start();
require_once 'connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Create new announcement
if ($_POST['action'] === 'create') {
    try {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $status = $_POST['status'];
        $priority = $_POST['priority'];
        
        // Handle file uploads
        $attachments = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $upload_dir = 'uploads/announcements/';
            
            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['attachments']['name'][$key];
                $file_path = $upload_dir . time() . '_' . $file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $attachments[] = $file_path;
                }
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO announcements (title, content, status, priority, created_by, attachments)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $title,
            $content,
            $status,
            $priority,
            $_SESSION['user_id'],
            !empty($attachments) ? json_encode($attachments) : null
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'Announcement created successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get all announcements
if ($_GET['action'] === 'get') {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.username as created_by_name 
            FROM announcements a 
            LEFT JOIN users u ON a.created_by = u.id 
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $announcements]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Update announcement
if ($_POST['action'] === 'update') {
    try {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $status = $_POST['status'];
        $priority = $_POST['priority'];
        
        // Handle new attachments
        $current_attachments = [];
        if (isset($_POST['existing_attachments'])) {
            $current_attachments = json_decode($_POST['existing_attachments'], true);
        }
        
        if (!empty($_FILES['attachments']['name'][0])) {
            $upload_dir = 'uploads/announcements/';
            
            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['attachments']['name'][$key];
                $file_path = $upload_dir . time() . '_' . $file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $current_attachments[] = $file_path;
                }
            }
        }
        
        $stmt = $pdo->prepare("
            UPDATE announcements 
            SET title = ?, content = ?, status = ?, priority = ?, attachments = ?
            WHERE id = ? AND created_by = ?
        ");
        
        $stmt->execute([
            $title,
            $content,
            $status,
            $priority,
            !empty($current_attachments) ? json_encode($current_attachments) : null,
            $id,
            $_SESSION['user_id']
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'Announcement updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Delete announcement
if ($_POST['action'] === 'delete') {
    try {
        $id = $_POST['id'];
        
        // Get attachments before deletion to clean up files
        $stmt = $pdo->prepare("SELECT attachments FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($announcement && $announcement['attachments']) {
            $attachments = json_decode($announcement['attachments'], true);
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ? AND created_by = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Announcement deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>