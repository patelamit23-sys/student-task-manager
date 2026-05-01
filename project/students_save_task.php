<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $due_date = $_POST['due_date'];
    $priority = strtolower($_POST['priority']);
    $category = $_POST['category'];
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, subject, due_date, priority, status, progress, created_at) VALUES (?, ?, ?, ?, ?, 'pending', 0, NOW())");
        $stmt->execute([$user_id, $title, $category, $due_date, $priority]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Task saved successfully!',
            'task_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        error_log("Error saving task: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>