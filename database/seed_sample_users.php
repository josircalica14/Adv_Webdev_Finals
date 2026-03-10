<?php
/**
 * Seed Sample Users for Featured Portfolios
 * Creates 5 sample users with diverse skills and portfolios
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../includes/Portfolio/PortfolioItemRepository.php';
require_once __DIR__ . '/../includes/Portfolio/Portfolio.php';
require_once __DIR__ . '/../includes/Portfolio/PortfolioItem.php';
require_once __DIR__ . '/../includes/FileStorageManager.php';

$db = Database::getInstance()->getConnection();
$authManager = new AuthenticationManager();

// Sample users data
$sampleUsers = [
    [
        'email' => 'maria.santos@example.com',
        'password' => 'Password123!',
        'full_name' => 'Maria Santos',
        'program' => 'BSIT',
        'bio' => 'Full-stack developer passionate about web technologies and UI/UX design',
        'skills' => ['JavaScript', 'React', 'Node.js', 'PHP', 'MySQL', 'UI/UX Design'],
        'soft_skills' => ['Team Leadership', 'Communication', 'Problem Solving'],
        'projects' => [
            [
                'title' => 'E-Commerce Platform',
                'description' => 'Built a full-featured online shopping platform with payment integration',
                'technologies' => 'React, Node.js, MongoDB, Stripe API',
                'url' => 'https://github.com/maria/ecommerce'
            ],
            [
                'title' => 'Task Management App',
                'description' => 'Collaborative task management tool with real-time updates',
                'technologies' => 'Vue.js, Firebase, Tailwind CSS',
                'url' => 'https://github.com/maria/taskapp'
            ]
        ]
    ],
    [
        'email' => 'john.reyes@example.com',
        'password' => 'Password123!',
        'full_name' => 'John Reyes',
        'program' => 'CSE',
        'bio' => 'AI/ML enthusiast specializing in computer vision and deep learning',
        'skills' => ['Python', 'TensorFlow', 'PyTorch', 'OpenCV', 'Data Science', 'Machine Learning'],
        'soft_skills' => ['Research', 'Critical Thinking', 'Presentation'],
        'projects' => [
            [
                'title' => 'Face Recognition System',
                'description' => 'Real-time face detection and recognition using deep learning',
                'technologies' => 'Python, OpenCV, TensorFlow, Keras',
                'url' => 'https://github.com/john/face-recognition'
            ],
            [
                'title' => 'Sentiment Analysis Tool',
                'description' => 'NLP-based sentiment analyzer for social media posts',
                'technologies' => 'Python, NLTK, Scikit-learn, Flask',
                'url' => 'https://github.com/john/sentiment-analyzer'
            ]
        ]
    ],
    [
        'email' => 'sarah.garcia@example.com',
        'password' => 'Password123!',
        'full_name' => 'Sarah Garcia',
        'program' => 'BSIT',
        'bio' => 'Mobile app developer with expertise in cross-platform development',
        'skills' => ['Flutter', 'Dart', 'React Native', 'Firebase', 'Mobile UI/UX', 'API Integration'],
        'soft_skills' => ['Creativity', 'Time Management', 'Adaptability'],
        'projects' => [
            [
                'title' => 'Fitness Tracker App',
                'description' => 'Cross-platform fitness tracking app with workout plans and nutrition tracking',
                'technologies' => 'Flutter, Firebase, Google Fit API',
                'url' => 'https://github.com/sarah/fitness-tracker'
            ],
            [
                'title' => 'Recipe Sharing Platform',
                'description' => 'Social platform for sharing and discovering recipes',
                'technologies' => 'React Native, Node.js, PostgreSQL',
                'url' => 'https://github.com/sarah/recipe-app'
            ]
        ]
    ],
    [
        'email' => 'david.cruz@example.com',
        'password' => 'Password123!',
        'full_name' => 'David Cruz',
        'program' => 'CSE',
        'bio' => 'Cybersecurity specialist and ethical hacker focused on web application security',
        'skills' => ['Cybersecurity', 'Penetration Testing', 'Python', 'Linux', 'Network Security', 'Cryptography'],
        'soft_skills' => ['Analytical Thinking', 'Attention to Detail', 'Ethical Judgment'],
        'projects' => [
            [
                'title' => 'Vulnerability Scanner',
                'description' => 'Automated web application vulnerability scanner',
                'technologies' => 'Python, Selenium, BeautifulSoup, SQLMap',
                'url' => 'https://github.com/david/vuln-scanner'
            ],
            [
                'title' => 'Password Manager',
                'description' => 'Secure password manager with encryption and 2FA',
                'technologies' => 'Python, AES Encryption, Qt Framework',
                'url' => 'https://github.com/david/password-manager'
            ]
        ]
    ],
    [
        'email' => 'anna.lopez@example.com',
        'password' => 'Password123!',
        'full_name' => 'Anna Lopez',
        'program' => 'BSIT',
        'bio' => 'Game developer and 3D graphics enthusiast creating immersive experiences',
        'skills' => ['Unity', 'C#', 'Unreal Engine', '3D Modeling', 'Game Design', 'Blender'],
        'soft_skills' => ['Creativity', 'Collaboration', 'Storytelling'],
        'projects' => [
            [
                'title' => 'Puzzle Adventure Game',
                'description' => '3D puzzle game with physics-based mechanics',
                'technologies' => 'Unity, C#, Blender, ProBuilder',
                'url' => 'https://github.com/anna/puzzle-game'
            ],
            [
                'title' => 'VR Training Simulator',
                'description' => 'Virtual reality training simulator for medical procedures',
                'technologies' => 'Unreal Engine, C++, Oculus SDK',
                'url' => 'https://github.com/anna/vr-simulator'
            ]
        ]
    ]
];

echo "Starting to seed sample users...\n\n";

foreach ($sampleUsers as $index => $userData) {
    echo "Creating user " . ($index + 1) . ": {$userData['full_name']}...\n";
    
    // Register user
    $result = $authManager->register(
        $userData['email'],
        $userData['password'],
        $userData['full_name'],
        $userData['program']
    );
    
    if (!$result['success']) {
        echo "  ❌ Failed to create user: " . ($result['error'] ?? 'Unknown error') . "\n";
        continue;
    }
    
    $userId = $result['userId'];
    echo "  ✓ User created with ID: {$userId}\n";
    
    // Update bio
    $stmt = $db->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->execute([$userData['bio'], $userId]);
    echo "  ✓ Bio updated\n";
    
    // Create portfolio manually using Portfolio entity
    $portfolio = new Portfolio\Portfolio(
        $userId,  // userId first
        true,     // is_public
        0,        // view_count
        null      // id
    );
    
    $portfolioRepo = new Portfolio\PortfolioRepository($db);
    $portfolioId = $portfolioRepo->create($portfolio);
    
    if (!$portfolioId) {
        echo "  ❌ Failed to create portfolio\n";
        continue;
    }
    
    echo "  ✓ Portfolio created with ID: {$portfolioId}\n";
    
    // Add projects using PortfolioItemRepository
    $itemRepo = new Portfolio\PortfolioItemRepository($db);
    
    foreach ($userData['projects'] as $projectIndex => $project) {
        $item = new Portfolio\PortfolioItem(
            $portfolioId,           // portfolioId
            'project',              // itemType
            $project['title'],      // title
            $project['description'], // description
            null,                   // itemDate
            explode(', ', $project['technologies']), // tags (technologies as array)
            [$project['url']],      // links
            true,                   // isVisible
            $projectIndex           // displayOrder
        );
        
        $itemId = $itemRepo->create($item);
        if (!$itemId) {
            echo "  ⚠ Failed to add project: {$project['title']}\n";
        }
    }
    echo "  ✓ Added " . count($userData['projects']) . " projects\n";
    
    // Add skills as achievements
    $skillsItem = new Portfolio\PortfolioItem(
        $portfolioId,
        'achievement',
        'Skills & Expertise',
        'Technical and soft skills',
        null,
        array_merge($userData['skills'], $userData['soft_skills']), // All skills as tags
        [],
        true,
        100
    );
    
    $skillsItemId = $itemRepo->create($skillsItem);
    if ($skillsItemId) {
        echo "  ✓ Added skills\n";
    } else {
        echo "  ⚠ Failed to add skills\n";
    }
    
    echo "  ✅ {$userData['full_name']} completed!\n\n";
}

echo "\n✅ Sample users seeding completed!\n";
echo "You can now login with any of these accounts using password: Password123!\n";
