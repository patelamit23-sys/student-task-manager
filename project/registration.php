<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration | Student Task Manager</title>
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

    .home-icon:hover {
      transform: scale(1.2);
    }

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
      transition: all 0.3s ease;
    }

    .login-container input:focus {
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 0 8px rgba(255, 138, 0, 0.5);
    }

    .login-container input.error {
      border: 2px solid #ff4444;
      background: rgba(255, 68, 68, 0.1);
    }

    .login-container input.success {
      border: 2px solid #00C851;
      background: rgba(0, 200, 81, 0.1);
    }

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

    .login-container p {
      margin-top: 15px;
      font-size: 14px;
    }

    .login-container a {
      color: #2918e8;
      text-decoration: none;
      font-weight: 500;
    }

    .login-container a:hover {
      text-decoration: underline;
    }

    .error-message {
      color: #ff4444;
      font-size: 12px;
      text-align: left;
      margin: -8px 0 8px 0;
      padding: 0 8px;
      display: none;
      font-weight: 500;
    }

    .success-message {
      color: #00C851;
      font-size: 14px;
      text-align: center;
      margin: 15px 0;
      padding: 10px;
      background: rgba(0, 200, 81, 0.1);
      border-radius: 8px;
      border: 1px solid #00C851;
      display: none;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <div class="heading">
      <a href="index.html" class="home-icon">🏠</a>
      <h2>Student Registration 🎓</h2>
    </div>

    <div class="success-message" id="successMessage"></div>

    <!-- Changed action from dashboard.php to register_process.php -->
    <form id="registrationForm" action="dashboard.php" method="POST" onsubmit="return handleRegistration(event)">
      <input type="text" name="username" id="username" placeholder="👤 Username" required>
      
      <input type="email" name="email" id="email" placeholder="📧 Email Address" required>
      
      <input type="password" name="password" id="password" placeholder="🔑 Password" required>
      
      <input type="password" name="confirmPassword" id="confirmPassword" placeholder="🔑 Confirm Password" required>
      <div class="error-message" id="confirmPasswordError"></div>

      <button type="submit" id="submitButton">Register 🚀</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>

<script>
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    // Reset previous states
    confirmPasswordInput.classList.remove('error', 'success');
    confirmPasswordError.style.display = 'none';

    // Check if passwords match
    if (password !== confirmPassword) {
        confirmPasswordInput.classList.add('error');
        confirmPasswordError.textContent = "Passwords do not match";
        confirmPasswordError.style.display = 'block';
        return false;
    } else {
        confirmPasswordInput.classList.add('success');
        return true;
    }
}

function handleRegistration(event) {
    event.preventDefault();
    
    // Validate form
    if (!validateForm()) {
        return false;
    }

    // Show success message
    const successMessage = document.getElementById('successMessage');
    const submitButton = document.getElementById('submitButton');
    
    successMessage.textContent = "✅ Registration Successful! Redirecting to dashboard...";
    successMessage.style.display = 'block';
    
    // Disable submit button
    submitButton.disabled = true;
    submitButton.textContent = "Registering...";

    // Submit the form after showing success message
    setTimeout(function() {
        document.getElementById('registrationForm').submit();
    }, 2000);

    return false;
}

// Real-time validation for password matching
document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    if (confirmPassword.length > 0) {
        if (password !== confirmPassword) {
            this.classList.add('error');
            this.classList.remove('success');
            confirmPasswordError.textContent = "Passwords do not match";
            confirmPasswordError.style.display = 'block';
        } else {
            this.classList.remove('error');
            this.classList.add('success');
            confirmPasswordError.style.display = 'none';
        }
    } else {
        this.classList.remove('error', 'success');
        confirmPasswordError.style.display = 'none';
    }
});
</script>
</body>
</html>