<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data - explicitly specify columns to avoid issues
$stmt = $pdo->prepare("SELECT id, username, full_name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle case where user is not found
if (!$user) {
    header("Location: login.php");
    exit();
}

// Initialize success message
$success_message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $user_id])) {
        $_SESSION['full_name'] = $full_name;
        $success_message = "Profile updated successfully!";
        
        // Refresh user data to show updated values
        $stmt = $pdo->prepare("SELECT id, username, full_name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $success_message = "Error updating profile. Please try again.";
    }
}

// Get statistics with error handling
try {
    $tasks_count = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
    $tasks_count->execute([$user_id]);
    $total_tasks = $tasks_count->fetchColumn() ?: 0;

    $completed_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed'");
    $completed_tasks->execute([$user_id]);
    $completed_count = $completed_tasks->fetchColumn() ?: 0;

    $pending_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'pending'");
    $pending_tasks->execute([$user_id]);
    $pending_count = $pending_tasks->fetchColumn() ?: 0;

    $study_time = $pdo->prepare("SELECT SUM(duration_minutes) FROM study_sessions WHERE user_id = ?");
    $study_time->execute([$user_id]);
    $total_study_minutes = $study_time->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Handle database errors gracefully
    $total_tasks = 0;
    $completed_count = 0;
    $pending_count = 0;
    $total_study_minutes = 0;
}

// Calculate completion percentage
$completion_percentage = $total_tasks > 0 ? round(($completed_count / $total_tasks) * 100) : 0;

// Handle missing created_at field
$member_since = 'January 1, 1970'; // Default value
if (isset($user['created_at']) && !empty($user['created_at'])) {
    $member_since = date('F j, Y', strtotime($user['created_at']));
} else {
    // If created_at doesn't exist, use registration_date or current date
    if (isset($user['registration_date'])) {
        $member_since = date('F j, Y', strtotime($user['registration_date']));
    } else {
        $member_since = 'Recently'; // Fallback text
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --success: #4bb543;
            --warning: #ffc107;
            --danger: #dc3545;
            --dark: #212529;
            --text: #333;
            --border-radius: 12px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        @media (max-width: 992px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 30px;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-header h3 {
            color: var(--primary);
            font-size: 1.5rem;
        }

        .card-header i {
            color: var(--accent);
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            background: #fafbfc;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.2);
            outline: none;
            background: white;
        }

        .form-control:disabled {
            background-color: #f5f7fa;
            color: #6c757d;
            border-color: #dbe1e8;
        }

        .form-text {
            display: block;
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6c757d;
            font-style: italic;
        }

        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .stat-card.tasks i { color: var(--primary); }
        .stat-card.completed i { color: var(--success); }
        .stat-card.pending i { color: var(--warning); }
        .stat-card.study i { color: var(--accent); }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
            color: var(--dark);
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .progress-container {
            margin-top: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: var(--border-radius);
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .progress-bar {
            height: 12px;
            background-color: #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 6px;
            transition: width 0.8s ease;
        }

        .member-since {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #eaeaea;
            text-align: center;
            color: #6c757d;
            font-size: 1rem;
        }

        .member-since i {
            margin-right: 10px;
            color: var(--accent);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: var(--transition);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        /* Success Message Styles */
        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
            box-shadow: var(--shadow);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 2px solid #f5c6cb;
            color: #721c24;
        }

        .alert i {
            font-size: 1.2rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back to Dashboard -->
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-user-cog"></i> Profile & Settings</h1>
            <p>Manage your account information and view your progress</p>
        </div>

        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
            <div class="alert <?php echo strpos($success_message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>" id="successMessage">
                <i class="fas <?php echo strpos($success_message, 'Error') !== false ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <!-- Profile Form -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-edit"></i>
                    <h3>Update Profile</h3>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Your Statistics</h3>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card tasks">
                        <i class="fas fa-tasks"></i>
                        <div class="stat-value"><?php echo $total_tasks; ?></div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-card completed">
                        <i class="fas fa-check-circle"></i>
                        <div class="stat-value"><?php echo $completed_count; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card pending">
                        <i class="fas fa-clock"></i>
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card study">
                        <i class="fas fa-graduation-cap"></i>
                        <div class="stat-value"><?php echo floor($total_study_minutes / 60); ?>h</div>
                        <div class="stat-label">Study Time</div>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="progress-label">
                        <span>Task Completion Rate</span>
                        <span><?php echo $completion_percentage; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $completion_percentage; ?>%"></div>
                    </div>
                </div>

                <div class="member-since">
                    <i class="fas fa-calendar-alt"></i>
                    Member since: <?php echo $member_since; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const fullName = document.querySelector('input[name="full_name"]');
                const email = document.querySelector('input[name="email"]');
                
                if (!fullName.value.trim()) {
                    e.preventDefault();
                    showError('Please enter your full name');
                    fullName.focus();
                    return;
                }
                
                if (!email.value.trim() || !isValidEmail(email.value)) {
                    e.preventDefault();
                    showError('Please enter a valid email address');
                    email.focus();
                    return;
                }
            });
            
            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            // Auto-hide success message after 5 seconds
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 500);
                }, 5000);
            }

            // Animate progress bar on load
            const progressFill = document.querySelector('.progress-fill');
            const width = progressFill.style.width;
            progressFill.style.width = '0';
            setTimeout(() => {
                progressFill.style.width = width;
            }, 300);

            // Function to show error messages
            function showError(message) {
                // Remove existing alerts
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                // Create new error alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-error';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>${message}</span>
                `;
                
                // Insert before the form
                const formCard = document.querySelector('.card');
                formCard.parentNode.insertBefore(alertDiv, formCard);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    alertDiv.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>