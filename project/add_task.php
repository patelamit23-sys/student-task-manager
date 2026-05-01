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
    $subject = $_POST['subject'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? '';
    $due_time = $_POST['due_time'] ?? '';
    
    if ($title) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, subject, priority, due_date, due_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $subject, $priority, $due_date, $due_time]);
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error adding task: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task - Student Life Manager</title>
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
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container { 
            max-width: 700px; 
            width: 100%;
            margin: 20px auto; 
            background: rgba(255, 255, 255, 0.95);
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--light);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .header p {
            color: var(--secondary);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .form-group { 
            margin-bottom: 25px; 
            position: relative;
        }
        
        .form-group label { 
            display: block; 
            margin-bottom: 10px; 
            color: var(--primary); 
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group label i {
            color: var(--secondary);
            width: 20px;
        }
        
        input, select, textarea { 
            width: 100%; 
            padding: 15px 20px; 
            border: 2px solid var(--border); 
            border-radius: 12px; 
            font-size: 16px; 
            background: var(--white);
            transition: all 0.3s ease;
            color: var(--dark);
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        textarea { 
            height: 120px; 
            resize: vertical; 
            line-height: 1.5;
        }
        
        .priority-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 5px;
        }
        
        .priority-option {
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--white);
            font-weight: 500;
        }
        
        .priority-option:hover {
            transform: translateY(-2px);
            border-color: var(--secondary);
        }
        
        .priority-option.selected {
            border-color: var(--secondary);
            background: var(--secondary);
            color: white;
        }
        
        .priority-low.selected {
            border-color: var(--success);
            background: var(--success);
        }
        
        .priority-medium.selected {
            border-color: var(--warning);
            background: var(--warning);
        }
        
        .priority-high.selected {
            border-color: var(--danger);
            background: var(--danger);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn { 
            padding: 15px 35px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 150px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #2980b9);
            color: white;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }
        
        .form-actions { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 40px; 
            padding-top: 30px;
            border-top: 2px solid var(--border);
        }
        
        .error { 
            color: var(--danger); 
            text-align: center; 
            margin-bottom: 20px; 
            background: rgba(231, 76, 60, 0.1);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--danger);
            font-weight: 500;
        }
        
        .success-message {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--success);
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .character-count {
            text-align: right;
            font-size: 0.8rem;
            color: var(--secondary);
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 30px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
        
        /* Animation for form elements */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            animation: slideIn 0.5s ease-out;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> Add New Task</h1>
            <p>Create a new task to stay organized and productive! ✨</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="taskForm">
            <div class="form-group">
                <label><i class="fas fa-heading"></i> Task Title *</label>
                <input type="text" name="title" required placeholder="Enter task title...">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description" placeholder="Describe your task... (Optional)"></textarea>
                <div class="character-count" id="descCount">0/500 characters</div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-book"></i> Subject</label>
                <input type="text" name="subject" placeholder="e.g., Mathematics, Science...">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-flag"></i> Priority</label>
                <div class="priority-options">
                    <div class="priority-option priority-low" data-value="low">
                        <i class="fas fa-arrow-down"></i> Low
                    </div>
                    <div class="priority-option priority-medium selected" data-value="medium">
                        <i class="fas fa-minus"></i> Medium
                    </div>
                    <div class="priority-option priority-high" data-value="high">
                        <i class="fas fa-arrow-up"></i> High
                    </div>
                </div>
                <input type="hidden" name="priority" id="priorityInput" value="medium">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-calendar-day"></i> Due Date</label>
                    <input type="date" name="due_date">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Due Time</label>
                    <input type="time" name="due_time">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
        </form>
    </div>

    <script>
        // Priority selection
        document.querySelectorAll('.priority-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.priority-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input value
                document.getElementById('priorityInput').value = this.getAttribute('data-value');
            });
        });

        // Character count for description
        const textarea = document.querySelector('textarea[name="description"]');
        const charCount = document.getElementById('descCount');
        
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count}/500 characters`;
            
            if (count > 500) {
                charCount.style.color = 'var(--danger)';
            } else if (count > 400) {
                charCount.style.color = 'var(--warning)';
            } else {
                charCount.style.color = 'var(--secondary)';
            }
        });

        // Form validation
        document.getElementById('taskForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a task title!');
                document.querySelector('input[name="title"]').focus();
                return false;
            }
            
            // Add loading state to button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Task...';
            submitBtn.disabled = true;
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[type="date"]').min = today;

        // Add some interactive effects
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>