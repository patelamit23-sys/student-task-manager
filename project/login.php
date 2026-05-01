<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and check user
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $row['password'])) {
            // Success: Save session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['username'] = $row['username'];

            // Show success message
            echo "<div style='
                    display:flex;
                    justify-content:center;
                    align-items:center;
                    height:100vh;
                    font-size:32px;
                    font-weight:bold;
                    color:green;
                    flex-direction:column;
                    text-align:center;
                '>
                ✅ Login Successful!<br>
                Redirecting to dashboard...
            </div>";

            // Redirect to dashboard after 2 seconds
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                  </script>";
            exit();
        } else {
            // ❌ Wrong password
            $error = "❌ Invalid password!";
        }
    } else {
        // ❌ Email not found
        $error = "❌ No account found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Student Task Manager</title>
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      background: url('college-login-bg.jpg') no-repeat center center/cover;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      color: #fff;
    }
    body::before {
      content: "";
      position: absolute;
      top:0; left:0; right:0; bottom:0;
      background: rgba(0,0,0,0.5);
      z-index: 0;
    }
    .login-container {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      padding: 40px 30px;
      border-radius: 20px;
      width: 380px;
      text-align: center;
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    .heading {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    .heading h2 {
      font-size: 28px;
      background: linear-gradient(90deg, #ff8a00, #e52e71);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 0;
    }
    .home-icon {
      font-size: 26px;
      text-decoration: none;
      color: #fff;
      transition: transform 0.2s;
    }
    .home-icon:hover { transform: scale(1.2); }
    .login-container input {
      width: 100%;
      padding: 12px;
      margin: 12px 0;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      outline: none;
      background: rgba(255, 255, 255, 0.85);
      color: #000;
    }
    .login-container input::placeholder { color: #555; }
    .login-container button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #ff512f, #dd2476);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 15px;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .login-container button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }
    .error-msg {
      color: #ff4b5c;
      font-weight: 600;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="heading">
      <a href="index.html" class="home-icon">🏠</a>
      <h2>Welcome Back 👋</h2>
    </div>
    <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
    <form action="" method="POST">
      <input type="email" name="email" placeholder="📧 Email Address" required>
      <input type="password" name="password" placeholder="🔑 Password" required>
      <button type="submit">Login 🚀</button>
    </form>
    
    <p>Do not have an account create now! <a href="registration.php">Sign  up</a></p>
  </div>
  <script src="app.js"></script>
</body>
</html>
