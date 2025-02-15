<?php
// candidate-process.php

require 'connection.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    // Retrieve form data; note: faculty is optional.
    $fullname = trim($_POST['fullname'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $faculty  = ($faculty === '') ? null : $faculty; // if empty, set to null
    $email    = trim($_POST['email'] ?? '');

    // Validate required fields (name, position, and email are required)
    if (empty($fullname) || empty($position) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in the required fields: Full Name, Position, and Email.'
        ]);
        exit;
    }

    // The candidate's photo is provided during registration,
    // so for admin adding, we set a default placeholder.
    $photo_path = '/api/placeholder/40/40';

    $stmt = $conn->prepare("INSERT INTO candidates (name, position, faculty, email, photo_path) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sssss", $fullname, $position, $faculty, $email, $photo_path);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'update') {
    // Update an existing candidate; requires candidate ID.
    $id       = trim($_POST['id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $faculty  = ($faculty === '') ? null : $faculty;
    $email    = trim($_POST['email'] ?? '');

    if (empty($id) || empty($fullname) || empty($position) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in the required fields: ID, Full Name, Position, and Email.'
        ]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE candidates SET name = ?, position = ?, faculty = ?, email = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssssi", $fullname, $position, $faculty, $email, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'delete') {
    // Delete a candidate by its ID.
    $id = trim($_POST['id'] ?? '');
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Candidate ID is required for deletion.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'get_total') {
    // Get the total number of candidates.
    $result = $conn->query("SELECT COUNT(*) as total FROM candidates");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['total' => $row['total']]);
    } else {
        echo json_encode(['total' => 0]);
    }
    exit;
}

if ($action === 'get') {
    // Retrieve candidates with optional filters.
    $search   = trim($_POST['search'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $position = trim($_POST['position'] ?? '');

    $query = "SELECT * FROM candidates WHERE 1 ";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $query .= " AND name LIKE ? ";
        $params[] = '%' . $search . '%';
        $types   .= 's';
    }
    if (!empty($faculty)) {
        $query .= " AND faculty = ? ";
        $params[] = $faculty;
        $types   .= 's';
    }
    if (!empty($position)) {
        $query .= " AND position = ? ";
        $params[] = $position;
        $types   .= 's';
    }

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode([]);
        exit;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
    echo json_encode($candidates);
    $stmt->close();
    exit;
}

// Optionally, add additional actions if needed.
?>
