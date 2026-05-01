<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit();
}
include 'db_connect.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Fetch student details
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows != 1){
        die("Student not found");
    }

    $student = $result->fetch_assoc();
}

// Update student
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $username, $email, $id);
    if($stmt->execute()){
        header("Location: admin_panel.php");
        exit();
    } else {
        $error = "Failed to update student.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Student</title>
<style>
body { font-family: 'Poppins', sans-serif; padding: 30px; background: #f0f2f5; }
form { background: white; padding: 20px; border-radius: 10px; width: 400px; }
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
button { padding: 10px 15px; background: #4b6cb7; color: white; border: none; border-radius: 5px; cursor: pointer; }
</style>
</head>
<body>
<h2>Edit Student</h2>
<form method="POST">
    <input type="text" name="username" value="<?php echo $student['username']; ?>" required>
    <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
    <button type="submit">Update Student</button>
</form>
<?php if(isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
