<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Fetch today's schedule (tasks and study sessions)
try {
    $schedule_query = "
        (SELECT 
            'task' as type,
            id,
            title,
            due_date as schedule_date,
            due_time as schedule_time,
            subject,
            priority,
            status,
            NULL as duration_minutes,
            CONCAT('Task: ', title) as display_text
         FROM tasks 
         WHERE user_id = ? AND due_date = ? AND status != 'completed')
        
        UNION ALL
        
        (SELECT 
            'study' as type,
            id,
            subject as title,
            session_date as schedule_date,
            start_time as schedule_time,
            subject,
            'medium' as priority,
            'scheduled' as status,
            duration_minutes,
            CONCAT('Study: ', subject) as display_text
         FROM study_sessions 
         WHERE user_id = ? AND session_date = ?)
         
        ORDER BY schedule_time ASC, type DESC
    ";
    
    $stmt = $pdo->prepare($schedule_query);
    $stmt->execute([$user_id, $today, $user_id, $today]);
    $today_schedule = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching schedule: " . $e->getMessage());
    $today_schedule = [];
}

// Calculate statistics
$total_items = count($today_schedule);
$completed_tasks = 0;
$upcoming_items = 0;

foreach ($today_schedule as $item) {
    if ($item['type'] == 'task' && $item['status'] == 'completed') {
        $completed_tasks++;
    }
    if (strtotime($item['schedule_time']) > time()) {
        $upcoming_items++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Schedule - Student Life Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #667eea;
            --primary-light: #8e9ef3;
            --secondary: #764ba2;
            --success: #4cc9f0;
            --warning: #ffd166;
            --danger: #f72585;
            --info: #06d6a0;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 2rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .btn-back {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .schedule-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .schedule-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .schedule-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .current-date {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .schedule-content {
            padding: 30px;
        }
        
        .schedule-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            border-left: 5px solid var(--primary);
            background: #f8f9ff;
            transition: all 0.3s ease;
        }
        
        .schedule-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .schedule-item.task {
            border-left-color: var(--info);
        }
        
        .schedule-item.study {
            border-left-color: var(--warning);
        }
        
        .schedule-item.completed {
            opacity: 0.7;
            background: #f0f0f0;
        }
        
        .schedule-time {
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            text-align: center;
            min-width: 100px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .time-main {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .time-period {
            font-size: 0.8rem;
            color: #666;
            margin-top: 2px;
        }
        
        .schedule-details {
            flex: 1;
        }
        
        .schedule-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .schedule-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        
        .schedule-type {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-task {
            background: rgba(6, 214, 160, 0.1);
            color: var(--info);
        }
        
        .type-study {
            background: rgba(255, 209, 102, 0.1);
            color: #e6a700;
        }
        
        .schedule-subject {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .schedule-priority {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .priority-high {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .priority-medium {
            background: rgba(255, 209, 102, 0.1);
            color: #e6a700;
        }
        
        .priority-low {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }
        
        .schedule-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-complete {
            background: var(--success);
            color: white;
        }
        
        .btn-edit {
            background: var(--primary);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: var(--primary);
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .empty-state p {
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .schedule-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .schedule-time {
                min-width: auto;
                width: 100%;
            }
            
            .schedule-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-day"></i> Today's Schedule</h1>
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_items; ?></div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $completed_tasks; ?></div>
                <div class="stat-label">Completed Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $upcoming_items; ?></div>
                <div class="stat-label">Upcoming Items</div>
            </div>
        </div>
        
        <div class="schedule-container">
            <div class="schedule-header">
                <h2><i class="fas fa-list-alt"></i> Schedule for Today</h2>
                <div class="current-date"><?php echo date('l, F j, Y'); ?></div>
            </div>
            
            <div class="schedule-content">
                <?php if (empty($today_schedule)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <h3>No Schedule for Today</h3>
                        <p>You're all caught up! Enjoy your free time or add some tasks/study sessions.</p>
                        <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
                            <a href="add_task.php" class="btn-primary">
                                <i class="fas fa-plus"></i> Add Task
                            </a>
                            <a href="study_timer.php" class="btn-primary">
                                <i class="fas fa-stopwatch"></i> Schedule Study
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($today_schedule as $item): 
                        $time_display = !empty($item['schedule_time']) ? date('g:i A', strtotime($item['schedule_time'])) : 'All Day';
                        $is_completed = $item['type'] == 'task' && $item['status'] == 'completed';
                        $type_class = $item['type'] == 'task' ? 'task' : 'study';
                        $type_text = $item['type'] == 'task' ? 'Task' : 'Study';
                    ?>
                        <div class="schedule-item <?php echo $type_class; ?> <?php echo $is_completed ? 'completed' : ''; ?>">
                            <div class="schedule-time">
                                <div class="time-main"><?php echo $time_display; ?></div>
                            </div>
                            
                            <div class="schedule-details">
                                <div class="schedule-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                
                                <div class="schedule-meta">
                                    <span class="schedule-type type-<?php echo $item['type']; ?>">
                                        <?php echo $type_text; ?>
                                    </span>
                                    
                                    <?php if (!empty($item['subject'])): ?>
                                        <span class="schedule-subject">
                                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($item['subject']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['type'] == 'task' && !empty($item['priority'])): ?>
                                        <span class="schedule-priority priority-<?php echo $item['priority']; ?>">
                                            <i class="fas fa-flag"></i> <?php echo ucfirst($item['priority']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['type'] == 'study' && !empty($item['duration_minutes'])): ?>
                                        <span class="schedule-subject">
                                            <i class="fas fa-clock"></i> <?php echo $item['duration_minutes']; ?> mins
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($item['type'] == 'task'): ?>
                                    <div class="schedule-actions">
                                        <?php if (!$is_completed): ?>
                                            <button class="btn btn-complete" onclick="markTaskComplete(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-check"></i> Mark Complete
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--success); font-weight: 600;">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </span>
                                        <?php endif; ?>
                                        <a href="edit_task.php?id=<?php echo $item['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function markTaskComplete(taskId) {
            if (confirm('Mark this task as completed?')) {
                fetch('update_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'task_id=' + encodeURIComponent(taskId) + '&status=completed&progress=100'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating task: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update task. Please try again.');
                });
            }
        }
        
        // Update current time every minute
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            document.querySelectorAll('.current-time').forEach(el => {
                el.textContent = timeString;
            });
        }
        
        setInterval(updateCurrentTime, 60000);
        updateCurrentTime();
    </script>
</body>
</html>