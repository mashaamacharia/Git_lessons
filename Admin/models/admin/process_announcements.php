<?php
// process_announcements.php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../controllers/connection.php'; // Assumes a MySQLi connection is created in $conn

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

switch ($action) {

    case 'create':
        // Read inputs
        $title    = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content  = isset($_POST['content']) ? trim($_POST['content']) : '';
        $status   = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
        $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'normal';
        // For created_by, if you have a login system, retrieve from session. Otherwise, using a default.
        $created_by = 'admin';
        $created_at = date('Y-m-d H:i:s');

        if(empty($title) || empty($content)){
            echo json_encode(['success'=>false, 'message'=>'Title and content are required.']);
            exit;
        }

        // Handle file attachments (if any)
        $attachments = [];
        if(!empty($_FILES['attachments']['name'][0])){
            $uploadDir = 'uploads/announcements/';
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            foreach($_FILES['attachments']['name'] as $key => $fileName){
                $tmpName   = $_FILES['attachments']['tmp_name'][$key];
                $fileExt   = pathinfo($fileName, PATHINFO_EXTENSION);
                $newName   = uniqid() . '.' . $fileExt;
                $destPath  = $uploadDir . $newName;
                if(move_uploaded_file($tmpName, $destPath)){
                    $attachments[] = $newName;
                }
            }
        }
        $attachments_json = json_encode($attachments);

        $sql = "INSERT INTO announcements (title, content, status, priority, created_by, created_at, attachments) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("sssssss", $title, $content, $status, $priority, $created_by, $created_at, $attachments_json);
            if($stmt->execute()){
                echo json_encode(['success'=>true, 'message'=>'Announcement created successfully.']);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Error executing query: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success'=>false, 'message'=>'Query Error: ' . $conn->error]);
        }
        break;

    case 'update':
        $announcement_id = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : 0;
        $title    = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content  = isset($_POST['content']) ? trim($_POST['content']) : '';
        $status   = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
        $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'normal';
        $updated_at = date('Y-m-d H:i:s');

        if($announcement_id <= 0 || empty($title) || empty($content)){
            echo json_encode(['success'=>false, 'message'=>'Invalid input.']);
            exit;
        }

        // Get current attachments from DB so that we can add new ones if provided.
        $currentAttachments = [];
        $sqlGet = "SELECT attachments FROM announcements WHERE id = ?";
        if($stmtGet = $conn->prepare($sqlGet)){
            $stmtGet->bind_param("i", $announcement_id);
            $stmtGet->execute();
            $resultGet = $stmtGet->get_result();
            if($row = $resultGet->fetch_assoc()){
                $currentAttachments = json_decode($row['attachments'], true) ?: [];
            }
            $stmtGet->close();
        }

        // Process new attachments (if any)
        if(!empty($_FILES['attachments']['name'][0])){
            $uploadDir = 'uploads/announcements/';
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            foreach($_FILES['attachments']['name'] as $key => $fileName){
                $tmpName   = $_FILES['attachments']['tmp_name'][$key];
                $fileExt   = pathinfo($fileName, PATHINFO_EXTENSION);
                $newName   = uniqid() . '.' . $fileExt;
                $destPath  = $uploadDir . $newName;
                if(move_uploaded_file($tmpName, $destPath)){
                    $currentAttachments[] = $newName;
                }
            }
        }
        $attachments_json = json_encode($currentAttachments);

        $sql = "UPDATE announcements SET title = ?, content = ?, status = ?, priority = ?, updated_at = ?, attachments = ? WHERE id = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ssssssi", $title, $content, $status, $priority, $updated_at, $attachments_json, $announcement_id);
            if($stmt->execute()){
                echo json_encode(['success'=>true, 'message'=>'Announcement updated successfully.']);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Error executing query: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success'=>false, 'message'=>'Query Error: ' . $conn->error]);
        }
        break;

    case 'delete':
        $announcement_id = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : 0;
        if($announcement_id <= 0){
            echo json_encode(['success'=>false, 'message'=>'Invalid announcement ID.']);
            exit;
        }

        // Optionally, you can delete associated attachment files from disk
        $sqlGet = "SELECT attachments FROM announcements WHERE id = ?";
        if($stmtGet = $conn->prepare($sqlGet)){
            $stmtGet->bind_param("i", $announcement_id);
            $stmtGet->execute();
            $resultGet = $stmtGet->get_result();
            if($row = $resultGet->fetch_assoc()){
                $attachments = json_decode($row['attachments'], true) ?: [];
                foreach($attachments as $file){
                    $filePath = 'uploads/announcements/' . $file;
                    if(file_exists($filePath)){
                        unlink($filePath);
                    }
                }
            }
            $stmtGet->close();
        }

        $sql = "DELETE FROM announcements WHERE id = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("i", $announcement_id);
            if($stmt->execute()){
                echo json_encode(['success'=>true, 'message'=>'Announcement deleted successfully.']);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Error deleting announcement: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success'=>false, 'message'=>'Query Error: ' . $conn->error]);
        }
        break;

    case 'get':
        $announcement_id = isset($_GET['announcement_id']) ? intval($_GET['announcement_id']) : 0;
        if($announcement_id <= 0){
            echo json_encode(['success'=>false, 'message'=>'Invalid announcement ID.']);
            exit;
        }

        $sql = "SELECT * FROM announcements WHERE id = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("i", $announcement_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            $stmt->close();
            if($announcement){
                // Decode attachments field
                $announcement['attachments'] = json_decode($announcement['attachments'], true);
                echo json_encode(['success'=>true, 'data'=>$announcement]);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Announcement not found.']);
            }
        } else {
            echo json_encode(['success'=>false, 'message'=>'Query Error: ' . $conn->error]);
        }
        break;

    case 'fetch':
        // Filters: search text, status filter and priority filter
        $search   = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status   = isset($_GET['status']) ? trim($_GET['status']) : '';
        $priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';

        $sql = "SELECT * FROM announcements WHERE 1";
        $types = "";
        $params = [];

        if(!empty($search)){
            $sql .= " AND (title LIKE ? OR content LIKE ?)";
            $searchParam = "%" . $search . "%";
            $types .= "ss";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        // Only filter by status if a value is provided and it's not 'all'
        if(!empty($status) && strtolower($status) != 'all'){
            $sql .= " AND status = ?";
            $types .= "s";
            $params[] = $status;
        }
        // Only filter by priority if a value is provided and it's not 'all'
        if(!empty($priority) && strtolower($priority) != 'all'){
            $sql .= " AND priority = ?";
            $types .= "s";
            $params[] = $priority;
        }

        $sql .= " ORDER BY created_at DESC";

        if($stmt = $conn->prepare($sql)){
            if(!empty($params)){
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $announcements = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            echo json_encode(['success'=>false, 'message'=>'Query Error: ' . $conn->error]);
            exit;
        }

        // Get stats counts
        $total = $conn->query("SELECT COUNT(*) as total FROM announcements")->fetch_assoc()['total'];
        $active = $conn->query("SELECT COUNT(*) as total FROM announcements WHERE status = 'active'")->fetch_assoc()['total'];
        $draft = $conn->query("SELECT COUNT(*) as total FROM announcements WHERE status = 'draft'")->fetch_assoc()['total'];

        echo json_encode([
            'success' => true,
            'data' => $announcements,
            'stats' => [
                'total'  => $total,
                'active' => $active,
                'draft'  => $draft
            ]
        ]);
        break;

    default:
        echo json_encode(['success'=>false, 'message'=>'Invalid action.']);
}

$conn->close();
?>
