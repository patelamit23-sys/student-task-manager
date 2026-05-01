<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - StudentLife</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --text: #2c3e50;
            --border: #bdc3c7;
            --white: #ffffff;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text);
            min-height: 100vh;
        }
        
        .header {
            background: var(--white);
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--border);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 700;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .nav-menu {
            display: flex;
            gap: 0.5rem;
            background: var(--light);
            padding: 0.5rem;
            border-radius: 10px;
        }
        
        .nav-item {
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: var(--text);
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover, .nav-item.active {
            background: var(--white);
            color: var(--secondary);
        }
        
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .page-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .task-input-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .task-input-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        input, select {
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--secondary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .tasks-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .task-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .task-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--border);
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .task-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .task-card.priority-high { border-left-color: var(--danger); }
        .task-card.priority-medium { border-left-color: var(--warning); }
        .task-card.priority-low { border-left-color: var(--success); }
        
        .task-card.completed {
            opacity: 0.7;
            background: #f8f9fa;
        }
        
        .task-card.completed .task-content {
            text-decoration: line-through;
        }
        
        .task-content {
            flex: 1;
        }
        
        .task-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .task-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
        }
        
        .task-actions .btn {
            padding: 8px 16px;
            font-size: 0.8rem;
        }
        
        .priority-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-high .priority-badge { background: var(--danger); color: white; }
        .priority-medium .priority-badge { background: var(--warning); color: white; }
        .priority-low .priority-badge { background: var(--success); color: white; }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ccc;
        }
        
        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #666;
        }
        
        @media (max-width: 968px) {
            .task-input-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .task-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .task-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1>Student<span>Life</span></h1>
            </div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tasks.php" class="nav-item active"><i class="fas fa-tasks"></i> Tasks</a>
                <a href="study.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Study</a>
                <a href="notes.php" class="nav-item"><i class="fas fa-sticky-note"></i> Notes</a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <div class="user-info">
                <h2>Welcome, <?php echo htmlspecialchars($full_name); ?></h2>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-tasks"></i> Task Manager</h1>
            <p>Organize your academic tasks and stay on track</p>
        </div>

        <!-- Add Task Section -->
        <div class="task-input-section">
            <div class="task-input-grid">
                <div class="form-group">
                    <label for="taskInput"><i class="fas fa-heading"></i> Task Title</label>
                    <input type="text" id="taskInput" placeholder="Enter task title...">
                </div>
                
                <div class="form-group">
                    <label for="dueDate"><i class="fas fa-calendar"></i> Due Date</label>
                    <input type="date" id="dueDate">
                </div>
                
                <div class="form-group">
                    <label for="priority"><i class="fas fa-flag"></i> Priority</label>
                    <select id="priority">
                        <option value="high">High Priority</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category"><i class="fas fa-tag"></i> Category</label>
                    <select id="category">
                        <option value="Assignment">Assignment</option>
                        <option value="Exam">Exam</option>
                        <option value="Project">Project</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>
                
                <button class="btn btn-primary" onclick="addTask()">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="tasks-container">
            <h3><i class="fas fa-list"></i> Your Tasks</h3>
            <div class="task-list" id="taskList">
                <!-- Tasks will be loaded here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        const taskList = document.getElementById("taskList");

        // Load tasks on page load
        window.onload = function() {
            loadTasks();
        };

        function loadTasks() {
            // Try to load from localStorage first
            const savedTasks = localStorage.getItem('studentTasks');
            if (savedTasks) {
                const tasks = JSON.parse(savedTasks);
                displayTasks(tasks);
            } else {
                showEmptyState();
            }
        }

        function addTask() {
            const taskInput = document.getElementById("taskInput");
            const dueDate = document.getElementById("dueDate");
            const priority = document.getElementById("priority");
            const category = document.getElementById("category");

            const taskText = taskInput.value.trim();
            if (taskText === "") {
                alert('Please enter a task title');
                return;
            }

            // Create task object
            const newTask = {
                id: Date.now(), // Unique ID
                title: taskText,
                due_date: dueDate.value,
                priority: priority.value,
                category: category.value,
                status: 'pending',
                created_at: new Date().toISOString()
            };

            // Save to localStorage
            saveTaskToStorage(newTask);

            // Clear inputs
            taskInput.value = "";
            dueDate.value = "";
            
            // Reload tasks
            loadTasks();
        }

        function saveTaskToStorage(newTask) {
            const savedTasks = localStorage.getItem('studentTasks');
            let tasks = [];
            
            if (savedTasks) {
                tasks = JSON.parse(savedTasks);
            }
            
            tasks.push(newTask);
            localStorage.setItem('studentTasks', JSON.stringify(tasks));
        }

        function displayTasks(tasks) {
            if (tasks.length === 0) {
                showEmptyState();
                return;
            }

            taskList.innerHTML = '';
            tasks.forEach(task => {
                displayTask(task);
            });
        }

        function displayTask(task) {
            const taskCard = document.createElement("div");
            taskCard.className = `task-card priority-${task.priority} ${task.status === 'completed' ? 'completed' : ''}`;
            taskCard.setAttribute('data-task-id', task.id);

            const taskContent = document.createElement("div");
            taskContent.className = "task-content";

            const taskTitle = document.createElement("div");
            taskTitle.className = "task-title";
            taskTitle.textContent = task.title;

            const taskMeta = document.createElement("div");
            taskMeta.className = "task-meta";
            
            const categorySpan = document.createElement("span");
            categorySpan.innerHTML = `<i class="fas fa-tag"></i> ${task.category}`;
            
            const dateSpan = document.createElement("span");
            dateSpan.innerHTML = `<i class="fas fa-calendar"></i> ${task.due_date || 'No deadline'}`;
            
            const prioritySpan = document.createElement("span");
            prioritySpan.className = "priority-badge";
            prioritySpan.textContent = task.priority;

            taskMeta.appendChild(categorySpan);
            taskMeta.appendChild(dateSpan);
            taskMeta.appendChild(prioritySpan);

            taskContent.appendChild(taskTitle);
            taskContent.appendChild(taskMeta);

            const taskActions = document.createElement("div");
            taskActions.className = "task-actions";

            const toggleBtn = document.createElement("button");
            toggleBtn.className = task.status === 'completed' ? 'btn btn-warning' : 'btn btn-success';
            toggleBtn.innerHTML = task.status === 'completed' ? '<i class="fas fa-undo"></i> Undo' : '<i class="fas fa-check"></i> Done';
            toggleBtn.onclick = () => toggleTaskStatus(task.id);

            const deleteBtn = document.createElement("button");
            deleteBtn.className = "btn btn-danger";
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
            deleteBtn.onclick = () => deleteTask(task.id);

            taskActions.appendChild(toggleBtn);
            taskActions.appendChild(deleteBtn);

            taskCard.appendChild(taskContent);
            taskCard.appendChild(taskActions);

            taskList.appendChild(taskCard);
        }

        function toggleTaskStatus(taskId) {
            const savedTasks = localStorage.getItem('studentTasks');
            if (savedTasks) {
                let tasks = JSON.parse(savedTasks);
                tasks = tasks.map(task => {
                    if (task.id === taskId) {
                        task.status = task.status === 'completed' ? 'pending' : 'completed';
                    }
                    return task;
                });
                localStorage.setItem('studentTasks', JSON.stringify(tasks));
                loadTasks();
            }
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                const savedTasks = localStorage.getItem('studentTasks');
                if (savedTasks) {
                    let tasks = JSON.parse(savedTasks);
                    tasks = tasks.filter(task => task.id !== taskId);
                    localStorage.setItem('studentTasks', JSON.stringify(tasks));
                    loadTasks();
                }
            }
        }

        function showEmptyState() {
            taskList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <h3>No tasks yet</h3>
                    <p>Add your first task to get started!</p>
                </div>
            `;
        }

        // Set minimum date to today
        document.getElementById('dueDate').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>