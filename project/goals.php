<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $target_date = $_POST['target_date'];
    $category = $_POST['category'];
    $progress = intval($_POST['progress']);
    
    if (empty($title) || empty($target_date)) {
        $error_message = "Title and target date are required!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO goals (user_id, title, description, target_date, progress, category, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$user_id, $title, $description, $target_date, $progress, $category]);
            $success_message = "Goal created successfully!";
            
            $_POST = array();
        } catch (PDOException $e) {
            $error_message = "Error creating goal. Please try again.";
            error_log("Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Goal - Student Life Manager</title>
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
            --danger: #f72585;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }
        
        .container {
            max-width: 800px;
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
        
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .required {
            color: var(--danger);
        }

        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        
        .success-message {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #4cc9f0;
            margin-bottom: 20px;
        }
        
        .success-text {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .redirect-text {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-bullseye"></i> Set New Goal</h1>
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <div class="form-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form id="goalForm" method="POST" action="">
                <div class="form-group">
                    <label for="title">Goal Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Maintain 7.0 GPA, Complete 5 Projects" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your goal and how you'll achieve it..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="target_date">Target Date <span class="required">*</span></label>
                        <input type="date" id="target_date" name="target_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['target_date']) ? htmlspecialchars($_POST['target_date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="academic" <?php echo (isset($_POST['category']) && $_POST['category'] == 'academic') ? 'selected' : ''; ?>>Academic</option>
                            <option value="personal" <?php echo (isset($_POST['category']) && $_POST['category'] == 'personal') ? 'selected' : ''; ?>>Personal</option>
                            <option value="career" <?php echo (isset($_POST['category']) && $_POST['category'] == 'career') ? 'selected' : ''; ?>>Career</option>
                            <option value="health" <?php echo (isset($_POST['category']) && $_POST['category'] == 'health') ? 'selected' : ''; ?>>Health</option>
                            <option value="other" <?php echo (isset($_POST['category']) && $_POST['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="progress">Initial Progress (%)</label>
                    <input type="range" id="progress" name="progress" min="0" max="100" value="<?php echo isset($_POST['progress']) ? htmlspecialchars($_POST['progress']) : '0'; ?>" oninput="document.getElementById('progress-value').textContent = this.value + '%'">
                    <div style="text-align: center; margin-top: 10px; font-weight: 600; color: var(--primary);" id="progress-value"><?php echo isset($_POST['progress']) ? htmlspecialchars($_POST['progress']) . '%' : '0%'; ?></div>
                </div>
                
                <button type="submit" class="btn-submit" id="submitButton">
                    <i class="fas fa-flag-checkered"></i> Create Goal
                </button>
            </form>
        </div>
    </div>

    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-message">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-text">Goal Created Successfully! 🎯</div>
            <div class="redirect-text">Redirecting to dashboard...</div>
        </div>
    </div>

    <script>
        document.getElementById('goalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const title = document.getElementById('title').value.trim();
            const targetDate = document.getElementById('target_date').value;
            
            if (!title || !targetDate) {
                alert('Please fill in all required fields (Title and Target Date).');
                return;
            }

            // Check if target date is in the future
            const today = new Date().toISOString().split('T')[0];
            if (targetDate < today) {
                alert('Target date must be in the future.');
                return;
            }
            
            // Disable submit button
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Goal...';
            
            // Submit form via AJAX
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Check if the response contains success message
                if (data.includes('Goal created successfully!')) {
                    // Show success overlay
                    const successOverlay = document.getElementById('successOverlay');
                    successOverlay.style.display = 'flex';
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    // If there was an error, reload the page to show PHP error message
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the goal. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-flag-checkered"></i> Create Goal';
            });
        });

        // Alternative method: If PHP success message is shown, redirect after delay
        <?php if ($success_message): ?>
            setTimeout(function() {
                window.location.href = 'dashboard.php';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>