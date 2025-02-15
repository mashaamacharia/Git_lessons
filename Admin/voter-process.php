<?php
session_start();
require_once 'connection.php';

class VoterManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getTotalVoters() {
        $query = "SELECT COUNT(*) as total FROM login";
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    public function addVoter($data) {
        try {
            // Check if registration number already exists
            $check = $this->conn->prepare("SELECT id FROM login WHERE Registration = ?");
            $check->bind_param("s", $data['registration']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Registration number already exists'];
            }
            
            $query = "INSERT INTO login (Registration, Name, Faculty, IDNo, HostelID, phone_number) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssss", 
                $data['registration'],
                $data['name'],
                $data['faculty'],
                $data['idno'],
                $data['hostelid'],
                $data['phone']
            );
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Voter added successfully'];
            }
            throw new Exception('Failed to add voter');
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getVoters($filters = []) {
        try {
            $query = "SELECT * FROM login WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($filters['faculty'])) {
                $query .= " AND Faculty = ?";
                $params[] = $filters['faculty'];
                $types .= "s";
            }
            
            if (isset($filters['status']) && $filters['status'] !== '') {
                $query .= " AND status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (Name LIKE ? OR Registration LIKE ? OR IDNo LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "sss";
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateVoter($data) {
        try {
            $query = "UPDATE login SET 
                      Name = ?, 
                      Faculty = ?, 
                      IDNo = ?, 
                      HostelID = ?, 
                      phone_number = ? 
                      WHERE Registration = ?";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssss",
                $data['name'],
                $data['faculty'],
                $data['idno'],
                $data['hostelid'],
                $data['phone'],
                $data['registration']
            );
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Voter updated successfully'];
            }
            throw new Exception('Failed to update voter');
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateStatus($registration, $status) {
        try {
            $query = "UPDATE login SET status = ? WHERE Registration = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ss", $status, $registration);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Status updated successfully'];
            }
            throw new Exception('Failed to update status');
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteVoter($registration) {
        try {
            $query = "DELETE FROM login WHERE Registration = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $registration);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Voter deleted successfully'];
            }
            throw new Exception('Failed to delete voter');
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voterManager = new VoterManager($conn);
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            echo json_encode($voterManager->addVoter($_POST));
            break;
            
        case 'update':
            echo json_encode($voterManager->updateVoter($_POST));
            break;
            
        case 'update_status':
            echo json_encode($voterManager->updateStatus(
                $_POST['registration'],
                $_POST['status']
            ));
            break;
            
        case 'delete':
            echo json_encode($voterManager->deleteVoter($_POST['registration']));
            break;
            
        case 'get':
            echo json_encode($voterManager->getVoters($_POST));
            break;
            
        case 'get_total':
            echo json_encode(['total' => $voterManager->getTotalVoters()]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>