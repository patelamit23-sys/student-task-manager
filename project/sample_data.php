<?php
require_once 'config/database.php';

// Create sample users
$users = [
    [
        'username' => 'student1',
        'email' => 'student1@example.com',
        'full_name' => 'John Smith',
        'password' => password_hash('password123', PASSWORD_DEFAULT)
    ],
    [
        'username' => 'john_doe',
        'email' => 'john.doe@example.com',
        'full_name' => 'John Doe',
        'password' => password_hash('password123', PASSWORD_DEFAULT)
    ]
];

foreach ($users as $user) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, full_name, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['username'], $user['email'], $user['full_name'], $user['password']]);
    } catch (PDOException $e) {
        echo "Error creating user {$user['username']}: " . $e->getMessage() . "\n";
    }
}

// Get user IDs
$user_ids = [];
$stmt = $pdo->query("SELECT id, username FROM users");
$user_ids = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($user_ids as $user_id => $username) {
    // Sample tasks
    $tasks = [
        [
            'Complete dashboard project', 
            'Finish the student dashboard with all features',
            'Computer Science',
            'high',
            'pending',
            75,
            date('Y-m-d', strtotime('+2 days'))
        ],
        [
            'Prepare for presentation',
            'Create slides and practice presentation',
            'Mathematics',
            'medium',
            'pending',
            30,
            date('Y-m-d', strtotime('+5 days'))
        ],
        [
            'Read chapter 5 of textbook',
            'Complete reading assignment',
            'Physics',
            'low',
            'completed',
            100,
            date('Y-m-d', strtotime('-1 day'))
        ]
    ];
    
    foreach ($tasks as $task) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO tasks (user_id, title, description, subject, priority, status, progress, due_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $task[0], $task[1], $task[2], $task[3], $task[4], $task[5], $task[6]]);
        } catch (PDOException $e) {
            echo "Error creating task: " . $e->getMessage() . "\n";
        }
    }
    
    // Sample study sessions
    $study_sessions = [
        ['Mathematics', 120, date('Y-m-d', strtotime('-1 day'))],
        ['Computer Science', 180, date('Y-m-d')],
        ['Physics', 90, date('Y-m-d', strtotime('-2 days'))]
    ];
    
    foreach ($study_sessions as $session) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO study_sessions (user_id, subject, duration_minutes, session_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $session[0], $session[1], $session[2]]);
        } catch (PDOException $e) {
            echo "Error creating study session: " . $e->getMessage() . "\n";
        }
    }
    
    // Sample goals
    $goals = [
        [
            'Achieve A grade in Mathematics',
            'Maintain above 90% in all assignments and exams',
            date('Y-m-d', strtotime('+30 days')),
            75
        ],
        [
            'Complete final project',
            'Finish the capstone project with all requirements',
            date('Y-m-d', strtotime('+45 days')),
            40
        ]
    ];
    
    foreach ($goals as $goal) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO goals (user_id, title, description, target_date, progress) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $goal[0], $goal[1], $goal[2], $goal[3]]);
        } catch (PDOException $e) {
            echo "Error creating goal: " . $e->getMessage() . "\n";
        }
    }
}

echo "Sample data created successfully!\n";
echo "You can now login with:\n";
echo "- Username: student1, Password: password123\n";
echo "- Username: john_doe, Password: password123\n";
?>