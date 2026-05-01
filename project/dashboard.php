<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Student';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Motivational quotes
$quotes = [
    "The future belongs to those who believe in the beauty of their dreams. - Eleanor Roosevelt",
    "Education is the most powerful weapon which you can use to change the world. - Nelson Mandela",
    "Don't let what you cannot do interfere with what you can do. - John Wooden",
    "The expert in anything was once a beginner. - Helen Hayes",
    "Your time is limited, don't waste it living someone else's life. - Steve Jobs",
    "The harder you work for something, the greater you'll feel when you achieve it.",
    "Dream it. Wish it. Do it.",
    "Success doesn't just find you. You have to go out and get it."
];

$random_quote = $quotes[array_rand($quotes)];

// Initialize arrays to prevent errors
$tasks = [];
$upcoming_tasks = [];
$study_sessions = [];
$recent_notes = [];
$today_schedule = [];
$study_goals = [];

try {
    // Fetch all data with error handling
    $tasks_stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC, due_time ASC");
    $tasks_stmt->execute([$user_id]);
    $tasks = $tasks_stmt->fetchAll(PDO::FETCH_ASSOC);

    $today = date('Y-m-d');
    $next_week = date('Y-m-d', strtotime('+7 days'));
    $upcoming_stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? AND due_date BETWEEN ? AND ? AND status != 'completed' ORDER BY due_date ASC LIMIT 5");
    $upcoming_stmt->execute([$user_id, $today, $next_week]);
    $upcoming_tasks = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Study sessions (last 7 days)
    $study_stmt = $pdo->prepare("SELECT subject, SUM(duration_minutes) as total_minutes FROM study_sessions WHERE user_id = ? AND session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY subject");
    $study_stmt->execute([$user_id]);
    $study_sessions = $study_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Today's Schedule - tasks and study sessions for today
    $schedule_stmt = $pdo->prepare("
        (SELECT 'task' as type, id, title, due_date, due_time, subject, priority FROM tasks 
         WHERE user_id = ? AND due_date = ? AND status != 'completed')
        UNION ALL
        (SELECT 'study' as type, id, subject as title, session_date as due_date, start_time as due_time, subject, 'medium' as priority FROM study_sessions 
         WHERE user_id = ? AND session_date = ?)
        ORDER BY due_time ASC
    ");
    $schedule_stmt->execute([$user_id, $today, $user_id, $today]);
    $today_schedule = $schedule_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Study Goals
    $goals_stmt = $pdo->prepare("SELECT * FROM study_goals WHERE user_id = ? AND target_date >= CURDATE() ORDER BY created_at DESC LIMIT 3");
    $goals_stmt->execute([$user_id]);
    $study_goals = $goals_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Continue with empty arrays - dashboard will show empty states
}

// Calculate statistics
$total_tasks = count($tasks);
$completed_tasks = 0;
$urgent_tasks = 0;
$today_tasks = 0;
$total_study_time = 0;

foreach ($tasks as $task) {
    if ($task['status'] == 'completed') $completed_tasks++;
    if ($task['priority'] == 'high' && $task['status'] != 'completed') $urgent_tasks++;
    if ($task['due_date'] == $today && $task['status'] != 'completed') $today_tasks++;
}

foreach ($study_sessions as $session) {
    $total_study_time += $session['total_minutes'];
}

$progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
$study_hours = floor($total_study_time / 60);
$study_minutes = $total_study_time % 60;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Life Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #7E8EF1;
            --primary-light: #A5B3F9;
            --secondary: #6DCDBB;
            --accent: #4AAE9B;
            --success: #7ED321;
            --warning: #FFB74D;
            --danger: #FF6B6B;
            --info: #4FC3F7;
            --light: #F8F9FF;
            --dark: #4A5568;
            --text: #4A5568;
            --text-light: #718096;
            --border: #E2E8F0;
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #7E8EF1 0%, #A5B3F9 100%);
            --gradient-2: linear-gradient(135deg, #FFB74D 0%, #FFCC80 100%);
            --gradient-3: linear-gradient(135deg, #6DCDBB 0%, #4AAE9B 100%);
            --gradient-4: linear-gradient(135deg, #7ED321 0%, #B8E986 100%);
            --gradient-5: linear-gradient(135deg, #FF6B6B 0%, #FFA5A5 100%);
            --gradient-6: linear-gradient(135deg, #4FC3F7 0%, #81D4FA 100%);
            --gradient-7: linear-gradient(135deg, #BA68C8 0%, #E1BEE7 100%);
        }
        
        body {
            background: linear-gradient(135deg, #F8F9FF 0%, #E6F0FF 100%);
            color: var(--text);
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: var(--white);
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(126, 142, 241, 0.1);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .nav-menu {
            display: flex;
            gap: 0.5rem;
            background: var(--light);
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .nav-item {
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: var(--text);
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item:hover, .nav-item.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(126, 142, 241, 0.2);
            transform: translateY(-2px);
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: var(--gradient-1);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(126, 142, 241, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
        }
        
        .welcome-content h1 {
            font-size: 2.4rem;
            margin-bottom: 0.5rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            font-style: italic;
            max-width: 70%;
        }
        
        .date-display {
            text-align: right;
            background: rgba(255,255,255,0.15);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .current-date {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .current-time {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 600;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(126, 142, 241, 0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(126, 142, 241, 0.15);
        }
        
        .stat-card.total-tasks::before { background: var(--gradient-1); }
        .stat-card.completed::before { background: var(--gradient-4); }
        .stat-card.urgent::before { background: var(--gradient-5); }
        .stat-card.today::before { background: var(--gradient-2); }
        .stat-card.study-time::before { background: var(--gradient-3); }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-icon.total-tasks { background: var(--gradient-1); }
        .stat-icon.completed { background: var(--gradient-4); }
        .stat-icon.urgent { background: var(--gradient-5); }
        .stat-icon.today { background: var(--gradient-2); }
        .stat-icon.study-time { background: var(--gradient-3); }
        
        .stat-info h3 {
            font-size: 0.9rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .panel {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(126, 142, 241, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            border: none;
        }
        
        .panel:hover {
            transform: translateY(-5px);
        }
        
        .panel-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--light) 0%, #f8f9fa 100%);
        }
        
        .panel-header h2 {
            font-size: 1.4rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
        }
        
        .panel-content {
            padding: 2rem;
            max-height: 500px;
            overflow-y: auto;
        }
        
        /* Task List */
        .task-item {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
            background: var(--white);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .task-item:hover {
            border-color: var(--primary);
            box-shadow: 0 6px 16px rgba(126, 142, 241, 0.15);
            transform: translateX(5px);
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .task-title {
            font-weight: 700;
            color: var(--text);
            font-size: 1.1rem;
        }
        
        .task-priority {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .priority-high { background: linear-gradient(135deg, #FFE6E6 0%, #FFCCCC 100%); color: var(--danger); border: 1px solid #FFCCCC; }
        .priority-medium { background: linear-gradient(135deg, #FFF4E6 0%, #FFE0B2 100%); color: var(--warning); border: 1px solid #FFE0B2; }
        .priority-low { background: linear-gradient(135deg, #E6F7FF 0%, #B3E0FF 100%); color: var(--info); border: 1px solid #B3E0FF; }
        
        .task-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .task-subject {
            background: var(--light);
            padding: 0.3rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .task-progress {
            height: 8px;
            background: var(--light);
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .progress-high { background: var(--danger); }
        .progress-medium { background: var(--warning); }
        .progress-low { background: var(--info); }
        .progress-completed { background: var(--success); }
        
        .task-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .btn {
            padding: 0.7rem 1.4rem;
            border-radius: 8px;
            border: none;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(126, 142, 241, 0.3);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
            box-shadow: 0 4px 12px rgba(126, 211, 33, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-light);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary:hover {
            background: var(--primary-light);
        }
        
        /* Schedule Item */
        .schedule-item {
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--white);
            border-left: 4px solid var(--info);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .schedule-item:hover {
            border-color: var(--info);
            box-shadow: 0 6px 16px rgba(79, 195, 247, 0.15);
            transform: translateX(5px);
        }
        
        .schedule-time {
            background: var(--light);
            padding: 0.5rem;
            border-radius: 8px;
            min-width: 70px;
            text-align: center;
            font-weight: 700;
            color: var(--info);
            font-size: 0.85rem;
        }
        
        .schedule-details {
            flex: 1;
        }
        
        .schedule-title {
            font-weight: 700;
            color: var(--text);
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .schedule-type {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .type-task { background: rgba(126, 142, 241, 0.1); color: var(--primary); }
        .type-study { background: rgba(109, 205, 187, 0.1); color: var(--secondary); }
        
        /* Goal Item */
        .goal-item {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            background: var(--white);
            border-left: 4px solid var(--warning);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .goal-item:hover {
            border-color: var(--warning);
            box-shadow: 0 6px 16px rgba(255, 183, 77, 0.15);
            transform: translateX(5px);
        }
        
        .goal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .goal-title {
            font-weight: 700;
            color: var(--text);
            font-size: 1.1rem;
        }
        
        .goal-subject {
            background: var(--light);
            padding: 0.3rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .goal-progress {
            height: 8px;
            background: var(--light);
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .goal-progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
            background: var(--warning);
        }
        
        .goal-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .goal-target {
            font-weight: 600;
            color: var(--text);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            color: var(--primary);
        }
        
        .empty-state h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .empty-state p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        
        /* Study Chart */
        .chart-container {
            height: 300px;
            margin-top: 1rem;
            position: relative;
        }
        
        /* Productivity Section */
        .productivity-section {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(126, 142, 241, 0.08);
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .productivity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .productivity-header h2 {
            font-size: 1.4rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
        }
        
        .progress-ring {
            width: 120px;
            height: 120px;
            position: relative;
        }
        
        .ring-bg {
            fill: none;
            stroke: var(--light);
            stroke-width: 8;
        }
        
        .ring-progress {
            fill: none;
            stroke: var(--success);
            stroke-width: 8;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dasharray 0.5s ease;
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        .progress-percent {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
        }
        
        .progress-label {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .welcome-section {
                text-align: center;
            }
            
            .welcome-content p {
                max-width: 100%;
            }
            
            .date-display {
                text-align: center;
                margin-top: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>Student<span>Life</span></h1>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tasks.php" class="nav-item"><i class="fas fa-tasks"></i> Tasks</a>
                <a href="study.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Study</a>
                <a href="notes.php" class="nav-item"><i class="fas fa-sticky-note"></i> Notes</a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <a href="today_schedule.php" class="nav-item">schedule something</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div class="welcome-content">
                    <h1>Welcome back, <?php echo htmlspecialchars($full_name); ?>! 👋</h1>
                    <p>✨ <?php echo htmlspecialchars($random_quote); ?></p>
                </div>
                <div class="date-display">
                    <div class="current-date"><?php echo date('l, F j, Y'); ?></div>
                    <div class="current-time" id="current-time">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total-tasks">
                <div class="stat-header">
                    <div class="stat-info">
                        <h3>Total Tasks</h3>
                        <div class="stat-number"><?php echo $total_tasks; ?></div>
                    </div>
                    <div class="stat-icon total-tasks">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-header">
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <div class="stat-number"><?php echo $completed_tasks; ?></div>
                    </div>
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card urgent">
                <div class="stat-header">
                    <div class="stat-info">
                        <h3>Urgent Tasks</h3>
                        <div class="stat-number"><?php echo $urgent_tasks; ?></div>
                    </div>
                    <div class="stat-icon urgent">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card today">
                <div class="stat-header">
                    <div class="stat-info">
                        <h3>Due Today</h3>
                        <div class="stat-number"><?php echo $today_tasks; ?></div>
                    </div>
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card study-time">
                <div class="stat-header">
                    <div class="stat-info">
                        <h3>Study Time (Week)</h3>
                        <div class="stat-number"><?php echo $study_hours; ?>h <?php echo $study_minutes; ?>m</div>
                    </div>
                    <div class="stat-icon study-time">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Tasks -->
                <div class="panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-tasks"></i> Recent Tasks & Assignments</h2>
                        <a href="tasks.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($tasks)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h3>No tasks yet</h3>
                                <p>Get started by adding your first task!</p>
                                <a href="add_task.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Add First Task
                                </a>
                            </div>
                        <?php else: ?>
                            <?php 
                            $display_tasks = array_slice($tasks, 0, 4);
                            foreach ($display_tasks as $task): 
                                $due_date = new DateTime($task['due_date']);
                                $today_date = new DateTime();
                                $interval = $today_date->diff($due_date);
                                $days_until_due = (int)$interval->format('%r%a');
                                
                                $time_left = "";
                                $is_urgent = false;
                                
                                if ($days_until_due == 0) {
                                    $time_left = "Today";
                                    $is_urgent = true;
                                } elseif ($days_until_due == 1) {
                                    $time_left = "Tomorrow";
                                    $is_urgent = true;
                                } elseif ($days_until_due > 1) {
                                    $time_left = "In $days_until_due days";
                                } else {
                                    $time_left = "Overdue!";
                                    $is_urgent = true;
                                }
                                
                                $progress_class = $task['status'] == 'completed' ? 'progress-completed' : 
                                                ($task['priority'] == 'high' ? 'progress-high' :
                                                ($task['priority'] == 'medium' ? 'progress-medium' : 'progress-low'));
                            ?>
                                <div class="task-item">
                                    <div class="task-header">
                                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="task-priority priority-<?php echo htmlspecialchars($task['priority']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($task['priority'])); ?>
                                            <?php if ($is_urgent): ?>
                                                <i class="fas fa-bolt" style="margin-left: 5px;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="task-meta">
                                        <span><i class="far fa-calendar"></i> <?php echo date('M j, Y', strtotime($task['due_date'])); ?></span>
                                        <span class="task-subject"><?php echo htmlspecialchars($task['subject']); ?></span>
                                        <span style="color: <?php echo $is_urgent ? 'var(--danger)' : 'var(--text-light)'; ?>; font-weight: 600;">
                                            <?php echo htmlspecialchars($time_left); ?>
                                        </span>
                                    </div>
                                    <div class="task-progress">
                                        <div class="progress-bar <?php echo $progress_class; ?>" style="width: <?php echo intval($task['progress']); ?>%;"></div>
                                    </div>
                                    <div class="task-actions">
                                        <span style="font-size: 0.8rem; color: var(--text-light); font-weight: 600;">
                                            Progress: <?php echo intval($task['progress']); ?>%
                                        </span>
                                        <?php if ($task['status'] != 'completed'): ?>
                                            <button class="btn btn-success" onclick="markComplete(<?php echo intval($task['id']); ?>)">
                                                <i class="fas fa-check"></i> Mark Complete
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--success); font-weight: 600;">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Study Progress -->
                <div class="panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-chart-bar"></i> Study Time Distribution</h2>
                        <a href="study.php" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($study_sessions)): ?>
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <h3>No study sessions</h3>
                                <p>Start tracking your study time!</p>
                                <a href="study_timer.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-stopwatch"></i> Start Studying
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="chart-container">
                                <canvas id="studyChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Today's Schedule -->
                <div class="panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-calendar-day"></i> Today's Schedule</h2>
                        <a href="todays_schedule.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Item
                        </a>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($today_schedule)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-check"></i>
                                <h3>Nothing scheduled</h3>
                                <p>Add tasks or study sessions for today!</p>
                                <a href="add_task.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Schedule Something
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($today_schedule as $item): 
                                $time_display = !empty($item['due_time']) ? date('g:i A', strtotime($item['due_time'])) : 'All Day';
                                $type_class = $item['type'] == 'task' ? 'type-task' : 'type-study';
                                $type_text = $item['type'] == 'task' ? 'Task' : 'Study';
                            ?>
                                <div class="schedule-item">
                                    <div class="schedule-time"><?php echo htmlspecialchars($time_display); ?></div>
                                    <div class="schedule-details">
                                        <div class="schedule-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="schedule-type <?php echo $type_class; ?>"><?php echo $type_text; ?></div>
                                        <?php if (!empty($item['subject'])): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($item['subject']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Study Goals -->
                <div class="panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-bullseye"></i> Study Goals</h2>
                        <a href="goals.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Goal
                        </a>
                    </div>
                    <div class="panel-content">
                        <?php if (empty($study_goals)): ?>
                            <div class="empty-state">
                                <i class="fas fa-bullseye"></i>
                                <h3>No goals set</h3>
                                <p>Set study goals to track your progress!</p>
                                <a href="goals.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Set a Goal
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($study_goals as $goal): 
                                $progress_percentage = $goal['target_hours'] > 0 ? round(($goal['completed_hours'] / $goal['target_hours']) * 100) : 0;
                                $progress_percentage = min($progress_percentage, 100);
                            ?>
                                <div class="goal-item">
                                    <div class="goal-header">
                                        <div class="goal-title"><?php echo htmlspecialchars($goal['title']); ?></div>
                                        <span style="font-size: 0.8rem; color: var(--text-light);">
                                            <?php echo $progress_percentage; ?>%
                                        </span>
                                    </div>
                                    <?php if (!empty($goal['subject'])): ?>
                                        <div class="goal-subject"><?php echo htmlspecialchars($goal['subject']); ?></div>
                                    <?php endif; ?>
                                    <div class="goal-meta">
                                        <span><i class="far fa-calendar"></i> <?php echo date('M j, Y', strtotime($goal['target_date'])); ?></span>
                                        <span class="goal-target"><?php echo $goal['completed_hours']; ?>/<?php echo $goal['target_hours']; ?> hrs</span>
                                    </div>
                                    <div class="goal-progress">
                                        <div class="goal-progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
        updateTime();

        // Task actions with improved error handling
        function markComplete(taskId) {
            if (confirm('Mark this task as completed?')) {
                fetch('update_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'task_id=' + encodeURIComponent(taskId) + '&status=completed&progress=100'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
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

        // Study Chart with improved initialization
        <?php if (!empty($study_sessions)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const chartCanvas = document.getElementById('studyChart');
            if (chartCanvas) {
                const ctx = chartCanvas.getContext('2d');
                const studyChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            <?php foreach ($study_sessions as $i => $session): ?>
                                '<?php echo addslashes($session['subject']); ?>'<?php echo $i < count($study_sessions) - 1 ? ',' : ''; ?>
                            <?php endforeach; ?>
                        ],
                        datasets: [{
                            data: [
                                <?php foreach ($study_sessions as $i => $session): ?>
                                    <?php echo intval($session['total_minutes']); ?><?php echo $i < count($study_sessions) - 1 ? ',' : ''; ?>
                            <?php endforeach; ?>
                            ],
                            backgroundColor: [
                                '#7E8EF1', '#6DCDBB', '#FFB74D', '#7ED321', '#FF6B6B', '#4FC3F7'
                            ],
                            borderWidth: 3,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const hours = Math.floor(value / 60);
                                        const minutes = value % 60;
                                        return label + ': ' + hours + 'h ' + minutes + 'm';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        <?php endif; ?>

        // Animate progress bars on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar, .goal-progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>
</html>