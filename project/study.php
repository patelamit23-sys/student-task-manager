<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Auto-create table if it doesn't exist (PHP code, not SQL)
try {
    $createTableSQL = "CREATE TABLE IF NOT EXISTS study_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        duration_minutes INT NOT NULL,
        session_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTableSQL);
} catch (PDOException $e) {
    die("Failed to create table: " . $e->getMessage());
}

// Handle add study session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_session'])) {
    $subject = $_POST['subject'];
    $duration = $_POST['duration'];
    $session_date = $_POST['session_date'];
    $notes = $_POST['notes'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO study_sessions (user_id, subject, duration_minutes, session_date, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $subject, $duration, $session_date, $notes]);
        header("Location: study.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error saving study session: " . $e->getMessage();
    }
}

// Fetch study sessions
try {
    $stmt = $pdo->prepare("SELECT * FROM study_sessions WHERE user_id = ? ORDER BY session_date DESC");
    $stmt->execute([$user_id]);
    $sessions = $stmt->fetchAll();

    // Calculate totals
    $total_time_stmt = $pdo->prepare("SELECT SUM(duration_minutes) as total FROM study_sessions WHERE user_id = ?");
    $total_time_stmt->execute([$user_id]);
    $total_time = $total_time_stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $error = "Error fetching study sessions: " . $e->getMessage();
    $sessions = [];
    $total_time = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Sessions - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-container, .sessions-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-full {
            grid-column: 1 / -1;
        }
        input, textarea, button {
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            width: 100%;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .session-item {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #667eea;
        }
        .error {
            background-color: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #c33;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Your sidebar here -->
    <div class="sidebar">
        <!-- Sidebar content -->
    </div>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Study Sessions</h1>
            <p>Total Study Time: <strong><?php echo floor($total_time / 60); ?> hours <?php echo $total_time % 60; ?> minutes</strong></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Log New Study Session</h3>
            <form method="POST">
                <div>
                    <input type="text" name="subject" placeholder="Subject (e.g., Mathematics, Physics)" required>
                </div>
                <div>
                    <input type="number" name="duration" placeholder="Duration (minutes)" required min="1">
                </div>
                <div>
                    <input type="date" name="session_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-full">
                    <textarea name="notes" placeholder="Notes (optional)" rows="3"></textarea>
                </div>
                <div class="form-full">
                    <button type="submit" name="add_session">
                        <i class="fas fa-save"></i> Save Study Session
                    </button>
                </div>
            </form>
        </div>

        <div class="sessions-container">
            <h3><i class="fas fa-history"></i> Recent Study Sessions</h3>
            <?php if (empty($sessions)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open" style="font-size: 3em; margin-bottom: 20px; color: #ccc;"></i>
                    <h3>No Study Sessions Yet</h3>
                    <p>Start by logging your first study session above!</p>
                </div>
            <?php else: ?>
                <?php foreach ($sessions as $session): ?>
                    <div class="session-item">
                        <h4><?php echo htmlspecialchars($session['subject']); ?></h4>
                        <p><i class="fas fa-clock"></i> <strong>Duration:</strong> <?php echo floor($session['duration_minutes'] / 60); ?>h <?php echo $session['duration_minutes'] % 60; ?>m</p>
                        <p><i class="fas fa-calendar"></i> <strong>Date:</strong> <?php echo date('F j, Y', strtotime($session['session_date'])); ?></p>
                        <?php if (!empty($session['notes'])): ?>
                            <p><i class="fas fa-sticky-note"></i> <strong>Notes:</strong> <?php echo htmlspecialchars($session['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>