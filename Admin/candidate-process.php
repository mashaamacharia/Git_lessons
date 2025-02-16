<?php
// candidate-process.php
header('Content-Type: application/json');

// Enable error reporting (for debugging only; disable on production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'connection.php'; // Make sure this file sets up a MySQLi connection as $conn

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

switch ($action) {
    case 'fetch':
        // Optional filters: search, faculty, and position
        $search   = isset($_GET['search']) ? trim($_GET['search']) : '';
        $faculty  = isset($_GET['faculty']) ? trim($_GET['faculty']) : '';
        $position = isset($_GET['position']) ? trim($_GET['position']) : '';

        $sql = "SELECT * FROM candidates WHERE 1";
        $types = "";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR registration_number LIKE ? OR email LIKE ?)";
            $searchParam = "%" . $search . "%";
            $types .= "sss";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        if (!empty($faculty)) {
            $sql .= " AND faculty = ?";
            $types .= "s";
            $params[] = $faculty;
        }
        if (!empty($position)) {
            $sql .= " AND position = ?";
            $types .= "s";
            $params[] = $position;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        // Prepare and execute statement
        if ($stmt = $conn->prepare($sql)) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $candidates = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
            exit;
        }
        
        // Get total count of candidates (ignoring filters)
        $totalResult = $conn->query("SELECT COUNT(*) as total FROM candidates");
        $totalRow = $totalResult->fetch_assoc();
        $total = $totalRow['total'];
        
        echo json_encode([
            'success' => true,
            'total'   => $total,
            'data'    => $candidates
        ]);
        break;

    case 'add':
        $name         = isset($_POST['name']) ? trim($_POST['name']) : '';
        $registration = isset($_POST['registration']) ? trim($_POST['registration']) : '';
        $position     = isset($_POST['position']) ? trim($_POST['position']) : '';
        $faculty      = isset($_POST['faculty']) ? trim($_POST['faculty']) : '';
        $email        = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (empty($name) || empty($registration) || empty($position) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        // New candidates default to active status ("active")
        $status = 'active';
        $created_at = date('Y-m-d H:i:s');

        $sql = "INSERT INTO candidates (name, position, faculty, email, status, registration_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            // Updated binding: all strings ("sssssss")
            $stmt->bind_param("sssssss", $name, $position, $faculty, $email, $status, $registration, $created_at);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Candidate added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
        }
        break;

    case 'get':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid candidate ID.']);
            exit;
        }

        $sql = "SELECT * FROM candidates WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidate = $result->fetch_assoc();
            $stmt->close();

            if ($candidate) {
                echo json_encode(['success' => true, 'data' => $candidate]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Candidate not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
        }
        break;

    case 'update':
        $id           = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name         = isset($_POST['name']) ? trim($_POST['name']) : '';
        $registration = isset($_POST['registration']) ? trim($_POST['registration']) : '';
        $position     = isset($_POST['position']) ? trim($_POST['position']) : '';
        $faculty      = isset($_POST['faculty']) ? trim($_POST['faculty']) : '';
        $email        = isset($_POST['email']) ? trim($_POST['email']) : '';

        if ($id <= 0 || empty($name) || empty($registration) || empty($position) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        $sql = "UPDATE candidates SET name = ?, registration_number = ?, position = ?, faculty = ?, email = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssi", $name, $registration, $position, $faculty, $email, $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Candidate updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error executing query: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
        }
        break;

    case 'delete':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid candidate ID.']);
            exit;
        }

        $sql = "DELETE FROM candidates WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Candidate deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting candidate: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
        }
        break;

    case 'ban':
        // Toggle candidate status between "active" and "inactive"
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid candidate ID.']);
            exit;
        }

        // Get current status
        $sql = "SELECT status FROM candidates WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidate = $result->fetch_assoc();
            $stmt->close();

            if (!$candidate) {
                echo json_encode(['success' => false, 'message' => 'Candidate not found.']);
                exit;
            }
            $currentStatus = strtolower($candidate['status']);
            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

            // Update candidate status
            $sqlUpdate = "UPDATE candidates SET status = ? WHERE id = ?";
            if ($stmt2 = $conn->prepare($sqlUpdate)) {
                $stmt2->bind_param("si", $newStatus, $id);
                if ($stmt2->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Candidate status updated to ' . $newStatus . '.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating candidate status: ' . $stmt2->error]);
                }
                $stmt2->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Query Error: ' . $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>
