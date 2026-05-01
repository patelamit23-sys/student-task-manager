<?php
session_start();
if(!isset($_SESSION['user_id'])) header("Location: login.php");
include "db.php";

$user_id = $_SESSION['user_id'];

if(isset($_POST['delete_account'])){
    $conn->query("DELETE FROM users WHERE id='$user_id'");
    session_destroy();
    header("Location: login.php");
}

$theme = $_COOKIE['theme'] ?? 'light';
if(isset($_POST['theme'])){
    $theme = $_POST['theme'];
    setcookie('theme', $theme, time()+3600*24*30, "/");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme; ?>">
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
<header><h1>Settings</h1></header>

<form method="POST">
<label>Theme:</label>
<select name="theme" onchange="this.form.submit()">
<option value="light" <?php if($theme=='light') echo "selected";?>>Light</option>
<option value="dark" <?php if($theme=='dark') echo "selected";?>>Dark</option>
</select>
</form>

<hr>
<form method="POST" onsubmit="return confirm('Are you sure? This will delete your account!')">
<input type="submit" name="delete_account" value="Delete Account" style="background:red; color:white; padding:10px;">
</form>
</div>
</body>
</html>

