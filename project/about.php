<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About | Student Task Manager</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: #f4f7f9;
      color: #333;
    }

    header {
      background: #2c3e50;
      color: #fff;
      padding: 1rem 2rem;
      text-align: center;
      position: relative; /* Needed for home icon positioning */
    }

    header h1 {
      margin: 0;
      font-size: 2rem;
    }

    /* Home icon styling */
    .home-icon {
      position: absolute;
      top: 1rem;
      right: 2rem;
      font-size: 1.8rem;
      text-decoration: none;
      color: #fff;
      background: #27ae60;
      padding: 0.4rem 0.7rem;
      border-radius: 50%;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .home-icon:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .about-container {
      max-width: 1000px;
      margin: 2rem auto;
      padding: 2rem;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .about-container h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 1rem;
      font-size: 1.8rem;
    }

    .about-text {
      font-size: 1.1rem;
      line-height: 1.8;
      text-align: center;
      margin-bottom: 2rem;
    }

    .card-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }

    .card {
      background: #f9fafb;
      padding: 1.5rem;
      border-radius: 12px;
      text-align: center;
      transition: 0.3s;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }

    .card h3 {
      color: #27ae60;
      margin-bottom: 0.5rem;
    }

    .card p {
      font-size: 0.95rem;
      color: #555;
    }

    footer {
      margin-top: 2rem;
      background: #2c3e50;
      color: #fff;
      text-align: center;
      padding: 1rem;
    }
  </style>
</head>
<body>

  <header>
    <h1>About Student Task Manager</h1>
    <a href="index.html" class="home-icon" title="Home">🏠</a>
  </header>

  <section class="about-container">
    <h2>Who We Are</h2>
    <p class="about-text">
      Student Task Manager is a web-based solution designed for college students to manage 
      their academic workload. From assignments and lab submissions to exams and seminars, 
      everything can be tracked in one place with ease.
    </p>

    <div class="card-section">
      <div class="card">
        <h3>🎯 Our Mission</h3>
        <p>To simplify student life by helping learners stay organized, reduce stress, 
        and never miss important deadlines.</p>
      </div>

      <div class="card">
        <h3>📅 Why It Matters</h3>
        <p>In college, balancing classes, projects, and personal activities is tough. 
        A central dashboard ensures productivity and smart time management.</p>
      </div>

      <div class="card">
        <h3>🚀 Future Scope</h3>
        <p>Upcoming features include AI-powered reminders, calendar integration, 
        team project collaboration, and a mobile app version.</p>
      </div>
    </div>
  </section>

  <footer>
    <p>💻 Built by Patel Amit | 3rd Year IT Engineering Student</p>
  </footer>
<script src="app.js"></script>
</body>
</html>



