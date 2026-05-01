<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit();
}
include 'db_connect.php';

// Handle new student addition
if(isset($_POST['add_student'])){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    header("Location: admin_panel.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f2f5; }
header { background: linear-gradient(90deg, #4b6cb7, #182848); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
header h2 { margin: 0; font-size: 24px; }
header a { color: white; text-decoration: none; font-weight: 500; }
.container { display: flex; }
.sidebar { width: 220px; background: #1f2a38; color: white; min-height: 100vh; padding: 20px; }
.sidebar h3 { font-weight: 500; margin-bottom: 30px; text-align: center; }
.sidebar a { display: flex; align-items: center; color: white; padding: 12px 15px; text-decoration: none; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; }
.sidebar a i { margin-right: 10px; }
.sidebar a:hover { background: #4b6cb7; }
.content { flex: 1; padding: 30px; }
.content h3 { margin-bottom: 20px; color: #333; }
table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 3px 15px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
table th, table td { padding: 12px 15px; text-align: left; }
table th { background: linear-gradient(90deg, #4b6cb7, #182848); color: white; }
table tr:hover { background: #f1f1f1; }
.btn { padding: 6px 12px; border: none; border-radius: 5px; color: white; cursor: pointer; margin-right: 5px; text-decoration: none; display: inline-block; }
.btn-edit { background: #f39c12; }
.btn-delete { background: #e74c3c; }
.btn-add { background: #2ecc71; margin-bottom: 20px; }
form { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
form input { padding: 10px; margin: 10px 0; width: 100%; border-radius: 5px; border: 1px solid #ccc; }
form button { padding: 10px 15px; border: none; border-radius: 5px; background: #4b6cb7; color: white; cursor: pointer; }
</style>
</head>
<body>
<header>
    <h2>Admin Dashboard</h2>
    <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</header>

<div class="container">
    <div class="sidebar">
        <h3><?php echo $_SESSION['admin_username']; ?> 👑</h3>
        <a href="admin_panel.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="content">
        <h3>Add New Student</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_student">Add Student</button>
        </form>

        <h3>Registered Students</h3>
        <?php
        $result = $conn->query("SELECT id, username, email FROM users"); 
        if($result->num_rows > 0){
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>";
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['username']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>
                        <a href='edit_student.php?id={$row['id']}' class='btn btn-edit'><i class='fa-solid fa-pen'></i> Edit</a>
                        <a href='delete_student.php?id={$row['id']}' class='btn btn-delete'><i class='fa-solid fa-trash'></i> Delete</a>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No students registered yet.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
