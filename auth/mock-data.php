<?php
/**
 * Mock Data for Prototype
 * This file contains hardcoded data to demonstrate the multi-user platform
 * Replace with real database queries in production
 */

// Mock users
$mockUsers = [
    1 => [
        'id' => 1,
        'email' => 'john.doe@example.com',
        'full_name' => 'John Doe',
        'username' => 'johndoe',
        'program' => 'BSIT',
        'bio' => 'Passionate web developer specializing in full-stack development with React and Node.js',
        'profile_photo' => 'images/profiles/john.jpg',
        'is_public' => true
    ],
    2 => [
        'id' => 2,
        'email' => 'jane.smith@example.com',
        'full_name' => 'Jane Smith',
        'username' => 'janesmith',
        'program' => 'CSE',
        'bio' => 'AI/ML enthusiast with experience in Python, TensorFlow, and data science',
        'profile_photo' => 'images/profiles/jane.jpg',
        'is_public' => true
    ],
    3 => [
        'id' => 3,
        'email' => 'mike.johnson@example.com',
        'full_name' => 'Mike Johnson',
        'username' => 'mikej',
        'program' => 'BSIT',
        'bio' => 'Mobile app developer focused on Flutter and React Native',
        'profile_photo' => 'images/profiles/mike.jpg',
        'is_public' => true
    ]
];

// Mock portfolio items
$mockPortfolioItems = [
    1 => [ // John's items
        [
            'id' => 1,
            'title' => 'E-Commerce Platform',
            'description' => 'Full-stack e-commerce solution with payment integration',
            'type' => 'project',
            'date' => '2024-01-15',
            'tags' => ['React', 'Node.js', 'MongoDB', 'Stripe'],
            'thumbnail' => 'images/projects/ecommerce.jpg'
        ],
        [
            'id' => 2,
            'title' => 'Dean\'s List Award',
            'description' => 'Achieved Dean\'s List for academic excellence',
            'type' => 'achievement',
            'date' => '2023-12-01',
            'tags' => ['Academic']
        ]
    ],
    2 => [ // Jane's items
        [
            'id' => 3,
            'title' => 'Image Classification Model',
            'description' => 'CNN-based image classifier with 95% accuracy',
            'type' => 'project',
            'date' => '2024-02-10',
            'tags' => ['Python', 'TensorFlow', 'Machine Learning'],
            'thumbnail' => 'images/projects/ml-model.jpg'
        ],
        [
            'id' => 4,
            'title' => 'Hackathon Winner',
            'description' => 'First place at University AI Hackathon 2024',
            'type' => 'achievement',
            'date' => '2024-01-20',
            'tags' => ['Competition', 'AI']
        ]
    ],
    3 => [ // Mike's items
        [
            'id' => 5,
            'title' => 'Fitness Tracker App',
            'description' => 'Cross-platform mobile app for fitness tracking',
            'type' => 'project',
            'date' => '2024-03-05',
            'tags' => ['Flutter', 'Firebase', 'Mobile'],
            'thumbnail' => 'images/projects/fitness-app.jpg'
        ]
    ]
];

// Mock customization settings
$mockCustomizations = [
    1 => [
        'theme' => 'default',
        'layout' => 'grid',
        'primary_color' => '#3498db',
        'accent_color' => '#e74c3c',
        'heading_font' => 'Roboto',
        'body_font' => 'Open Sans'
    ],
    2 => [
        'theme' => 'dark',
        'layout' => 'list',
        'primary_color' => '#9b59b6',
        'accent_color' => '#f39c12',
        'heading_font' => 'Montserrat',
        'body_font' => 'Lato'
    ],
    3 => [
        'theme' => 'minimal',
        'layout' => 'timeline',
        'primary_color' => '#2ecc71',
        'accent_color' => '#e67e22',
        'heading_font' => 'Poppins',
        'body_font' => 'Inter'
    ]
];

// Simple session management
function mockLogin($email, $password) {
    global $mockUsers;
    
    // Hardcoded credentials for demo
    $validCredentials = [
        'john.doe@example.com' => 'password123',
        'jane.smith@example.com' => 'password123',
        'mike.johnson@example.com' => 'password123'
    ];
    
    if (isset($validCredentials[$email]) && $validCredentials[$email] === $password) {
        foreach ($mockUsers as $user) {
            if ($user['email'] === $email) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                return true;
            }
        }
    }
    
    return false;
}

function mockRegister($email, $password, $fullName, $program) {
    // In prototype, just create session
    $newUserId = 999; // Mock ID
    $username = strtolower(str_replace(' ', '', $fullName));
    
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['program'] = $program;
    
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $mockUsers;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    return $mockUsers[$userId] ?? [
        'id' => $userId,
        'username' => $_SESSION['username'] ?? 'user',
        'full_name' => $_SESSION['full_name'] ?? 'User',
        'program' => $_SESSION['program'] ?? 'BSIT',
        'email' => 'user@example.com',
        'bio' => 'New user',
        'is_public' => false
    ];
}

function getPublicPortfolios() {
    global $mockUsers;
    return array_filter($mockUsers, function($user) {
        return $user['is_public'];
    });
}

function getPortfolioItems($userId) {
    global $mockPortfolioItems;
    return $mockPortfolioItems[$userId] ?? [];
}

function getCustomization($userId) {
    global $mockCustomizations;
    return $mockCustomizations[$userId] ?? [
        'theme' => 'default',
        'layout' => 'grid',
        'primary_color' => '#3498db',
        'accent_color' => '#e74c3c',
        'heading_font' => 'Roboto',
        'body_font' => 'Open Sans'
    ];
}

function mockLogout() {
    session_destroy();
}
?>
