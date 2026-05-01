<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $event_type = $_POST['event_type'] ?? '';
    
    if ($title && $event_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (user_id, title, description, event_date, event_time, event_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $event_date, $event_time, $event_type]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error adding event: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Student Life Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 20px; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        textarea { height: 100px; resize: vertical; }
        .btn { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-right: 10px; }
        .btn-outline { background: transparent; border: 2px solid #667eea; color: #667eea; }
        .form-actions { display: flex; justify-content: space-between; margin-top: 30px; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Event</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Event Title *</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            
            <div class="form-group">
                <label>Event Date *</label>
                <input type="date" name="event_date" required>
            </div>
            
            <div class="form-group">
                <label>Event Time</label>
                <input type="time" name="event_time">
            </div>
            
            <div class="form-group">
                <label>Event Type</label>
                <select name="event_type">
                    <option value="class">Class</option>
                    <option value="study">Study Session</option>
                    <option value="meeting">Meeting</option>
                    <option value="exam">Exam</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn">Add Event</button>
            </div>
        </form>
    </div>
</body>
</html>