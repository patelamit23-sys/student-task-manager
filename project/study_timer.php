<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'] ?? '';
    $duration_minutes = $_POST['duration_minutes'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    if ($subject && $duration_minutes > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO study_sessions (user_id, subject, duration_minutes, session_date, notes) VALUES (?, ?, ?, CURDATE(), ?)");
            $stmt->execute([$_SESSION['user_id'], $subject, $duration_minutes, $notes]);
            $success = "Study session recorded successfully!";
        } catch (PDOException $e) {
            $error = "Error recording study session: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Timer - Student Life Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 20px; color: #333; text-align: center; }
        .timer-display { text-align: center; font-size: 3rem; margin: 30px 0; color: #667eea; }
        .timer-controls { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #4ecdc4; color: white; }
        .btn-danger { background: #f72585; color: white; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: 500; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        textarea { height: 100px; resize: vertical; }
        .success { color: #4ecdc4; text-align: center; margin-bottom: 15px; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Study Timer</h1>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="timer-display" id="timerDisplay">00:00:00</div>
        
        <div class="timer-controls">
            <button class="btn btn-primary" onclick="startTimer()">Start</button>
            <button class="btn btn-danger" onclick="stopTimer()">Stop</button>
            <button class="btn btn-success" onclick="resetTimer()">Reset</button>
        </div>
        
        <form method="POST" id="studyForm">
            <div class="form-group">
                <label>Subject *</label>
                <input type="text" name="subject" id="subject" required>
            </div>
            
            <div class="form-group">
                <label>Study Duration (minutes) *</label>
                <input type="number" name="duration_minutes" id="duration_minutes" required readonly>
            </div>
            
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" placeholder="What did you study?"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Study Session</button>
        </form>
        
        <a href="dashboard.php" style="display: block; text-align: center; margin-top: 20px; color: #667eea;">← Back to Dashboard</a>
    </div>

    <script>
        let startTime;
        let elapsedTime = 0;
        let timerInterval;
        let isRunning = false;

        function updateDisplay() {
            const totalSeconds = Math.floor(elapsedTime / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            document.getElementById('timerDisplay').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Update duration in minutes for form
            document.getElementById('duration_minutes').value = Math.floor(totalSeconds / 60);
        }

        function startTimer() {
            if (!isRunning) {
                startTime = Date.now() - elapsedTime;
                timerInterval = setInterval(() => {
                    elapsedTime = Date.now() - startTime;
                    updateDisplay();
                }, 1000);
                isRunning = true;
            }
        }

        function stopTimer() {
            if (isRunning) {
                clearInterval(timerInterval);
                isRunning = false;
            }
        }

        function resetTimer() {
            stopTimer();
            elapsedTime = 0;
            updateDisplay();
            document.getElementById('duration_minutes').value = 0;
        }

        // Initialize display
        updateDisplay();
    </script>
</body>
</html>