<?php
session_start();
if(!isset($_SESSION['user_id'])) header("Location: login.php");
include "db.php";

$user_id = $_SESSION['user_id'];
$task_id = intval($_GET['id']);

$res = $conn->query("SELECT * FROM tasks WHERE id='$task_id' AND user_id='$user_id'");
if($res->num_rows==0) header("Location: tasks.php");
$task = $res->fetch_assoc();

if($_SERVER['REQUEST_METHOD']=="POST"){
    $title = $_POST['title'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $progress = intval($_POST['progress']);

    $stmt = $conn->prepare("UPDATE tasks SET title=?, due_date=?, priority=?, status=?, progress=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssssii",$title,$due_date,$priority,$status,$progress,$task_id,$user_id);
    $stmt->execute();
    header("Location: tasks.php");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Task</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
<h2>📚 Student<br>Manager</h2>
<a href="dashboard.php">🏠 Dashboard</a>
<a href="tasks.php">📝 Tasks</a>
<a href="calendar.php">📅 Calendar</a>
<a href="profile.php">👤 Profile</a>
<a href="settings.php">⚙️ Settings</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
<header><h1>Edit Task</h1></header>

<form method="POST">
<label>Title:</label><br>
<input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>
<label>Due Date:</label><br>
<input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required><br>
<label>Priority:</label><br>
<select name="priority">
<option value="low" <?php if($task['priority']=='low') echo "selected";?>>Low</option>
<option value="medium" <?php if($task['priority']=='medium') echo "selected";?>>Medium</option>
<option value="high" <?php if($task['priority']=='high') echo "selected";?>>High</option>
</select><br>
<label>Status:</label><br>
<select name="status">
<option value="Not Started" <?php if($task['status']=='Not Started') echo "selected";?>>Not Started</option>
<option value="Started" <?php if($task['status']=='Started') echo "selected";?>>Started</option>
<option value="In Progress" <?php if($task['status']=='In Progress') echo "selected";?>>In Progress</option>
<option value="Completed" <?php if($task['status']=='Completed') echo "selected";?>>Completed</option>
</select><br>
<label>Progress %:</label><br>
<input type="number" name="progress" value="<?php echo $task['progress']; ?>" min="0" max="100"><br><br>
<input type="submit" value="Update Task">
</form>
</div>
</body>
</html>
