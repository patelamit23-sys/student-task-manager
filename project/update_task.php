<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    try {
        // Toggle status between pending and completed
        $stmt = $pdo->prepare("SELECT status FROM student_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $task = $stmt->fetch();
        
        if ($task) {
            $new_status = $task['status'] === 'completed' ? 'pending' : 'completed';
            $progress = $new_status === 'completed' ? 100 : 0;
            
            $update_stmt = $pdo->prepare("UPDATE student_tasks SET status = ?, progress = ? WHERE id = ? AND user_id = ?");
            $update_stmt->execute([$new_status, $progress, $task_id, $user_id]);
            
            echo json_encode(['success' => true, 'new_status' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found']);
        }
    } catch (PDOException $e) {
        error_log("Update task error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>