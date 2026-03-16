<?php

namespace Database\Seeders;

use App\Models\CustomizationSettings;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    // Per-student items: [type, title, description, tags, date]
    private array $studentItems = [
        // student2 — Maria Santos (BSIT, web/UI)
        'student2' => [
            ['project',     'Personal Portfolio Website',   'Designed and built a responsive portfolio site using HTML, CSS, and JavaScript with a focus on accessibility and performance.', ['HTML', 'CSS', 'JavaScript', 'Figma'], '2024-03-01'],
            ['project',     'University Event Booking App', 'Web app for booking university events with real-time seat availability using Laravel and Vue.js.',                              ['Laravel', 'Vue.js', 'MySQL'],          '2024-06-15'],
            ['achievement', 'Best UI/UX Award — Capstone',  'Received Best UI/UX Design award at the department capstone presentation.',                                                    ['Design', 'Award'],                     '2024-05-20'],
            ['milestone',   'Completed Google UX Certificate', 'Finished the Google UX Design Professional Certificate on Coursera.',                                                      ['UX', 'Certification'],                 '2023-11-10'],
            ['skill',       'UI/UX Design',                 'Proficient in Figma, Adobe XD, and user research methodologies.',                                                             ['Figma', 'Adobe XD', 'Prototyping'],    null],
        ],
        // student3 — Juan dela Cruz (BSIT, full-stack)
        'student3' => [
            ['project',     'Smart Campus System',          'IoT-based campus monitoring system tracking room occupancy and energy usage via MQTT and a Laravel dashboard.',               ['Laravel', 'IoT', 'MQTT', 'Vue.js'],    '2024-07-01'],
            ['project',     'Online Voting Platform',       'Secure online voting system with OTP verification and audit logs, built for the student council elections.',                  ['PHP', 'MySQL', 'Security'],            '2023-10-05'],
            ['achievement', 'Dean\'s List — 2nd Year',      'Achieved Dean\'s List recognition for the 2nd academic year.',                                                               ['Academic'],                            '2023-06-01'],
            ['milestone',   'First Freelance Project',      'Completed first paid freelance project — a company profile website for a local business.',                                   ['Freelance', 'Professional'],           '2023-08-20'],
            ['skill',       'Full-Stack Web Development',   'Experienced with Laravel, React, MySQL, and REST API design.',                                                               ['Laravel', 'React', 'MySQL', 'API'],    null],
        ],
        // student4 — Angela Reyes (BSIT, backend/data)
        'student4' => [
            ['project',     'Student Records Management System', 'Database-driven system for managing student academic records with role-based access control.',                          ['PHP', 'MySQL', 'RBAC'],                '2024-04-10'],
            ['project',     'Data Pipeline for Enrollment Analytics', 'ETL pipeline that processes enrollment data and generates visual reports using Python and Pandas.',                ['Python', 'Pandas', 'ETL', 'SQL'],      '2024-08-01'],
            ['achievement', 'Regional IT Quiz Bee — 2nd Place', 'Placed 2nd in the regional IT Quiz Bee competition.',                                                                   ['Competition', 'Award'],                '2023-09-15'],
            ['milestone',   'AWS Cloud Practitioner Certified',  'Passed the AWS Certified Cloud Practitioner exam.',                                                                     ['AWS', 'Cloud', 'Certification'],       '2024-01-20'],
            ['skill',       'Database Administration',       'Skilled in MySQL, PostgreSQL, query optimization, and database design.',                                                    ['MySQL', 'PostgreSQL', 'SQL'],          null],
        ],
        // student5 — Carlo Mendoza (BSIT, mobile)
        'student5' => [
            ['project',     'FitTrack Mobile App',          'Cross-platform fitness tracker built with Flutter, integrating Google Fit API for workout and nutrition logging.',           ['Flutter', 'Firebase', 'Google Fit'],   '2024-05-01'],
            ['project',     'Campus Shuttle Tracker',       'Real-time shuttle tracking app using GPS and Firebase Realtime Database.',                                                   ['Flutter', 'Firebase', 'GPS'],          '2023-12-10'],
            ['achievement', 'Hackathon Champion — TechFest 2024', 'Won 1st place at TechFest 2024 university hackathon with the FitTrack concept.',                                      ['Hackathon', 'Award'],                  '2024-02-28'],
            ['achievement', 'Dean\'s List — 3rd Year',      'Achieved Dean\'s List for the 3rd academic year.',                                                                          ['Academic'],                            '2024-06-01'],
            ['milestone',   'Published App on Play Store',  'Successfully published the Campus Shuttle Tracker on Google Play Store.',                                                   ['Mobile', 'Published'],                 '2024-03-15'],
        ],
        // student6 — Sofia Lim (CSE, ML/AI)
        'student6' => [
            ['project',     'Plant Disease Detection Model', 'CNN-based image classification model for detecting plant diseases from leaf photos, achieving 94% accuracy.',               ['Python', 'TensorFlow', 'CNN', 'CV'],   '2024-06-20'],
            ['project',     'NLP Sentiment Analyzer',       'Sentiment analysis tool for Filipino social media text using fine-tuned BERT.',                                              ['Python', 'NLP', 'BERT', 'HuggingFace'],'2024-03-05'],
            ['achievement', 'Best Research Paper — CS Dept', 'Awarded Best Research Paper for the plant disease detection study.',                                                        ['Research', 'Award'],                   '2024-05-15'],
            ['milestone',   'Research Assistant — AI Lab',  'Appointed as research assistant at the university AI laboratory.',                                                          ['Research', 'AI'],                      '2023-09-01'],
            ['skill',       'Machine Learning & AI',        'Proficient in TensorFlow, PyTorch, scikit-learn, and computer vision pipelines.',                                           ['TensorFlow', 'PyTorch', 'Python'],     null],
        ],
        // student7 — Rafael Torres (CSE, systems)
        'student7' => [
            ['project',     'Mini OS Kernel',               'Implemented a minimal x86 OS kernel with memory management, process scheduling, and a basic shell in C.',                   ['C', 'Assembly', 'OS', 'x86'],          '2024-04-01'],
            ['project',     'Competitive Programming Judge', 'Online judge system supporting C++, Python, and Java submissions with sandboxed execution.',                               ['C++', 'Docker', 'Python'],             '2023-11-20'],
            ['achievement', 'ICPC Regional Qualifier',      'Qualified for the ICPC Asia Regional contest representing the university.',                                                  ['Competitive Programming', 'ICPC'],     '2023-10-01'],
            ['milestone',   '500 LeetCode Problems Solved', 'Reached 500 solved problems on LeetCode with a focus on dynamic programming and graph algorithms.',                         ['Algorithms', 'LeetCode'],              '2024-02-01'],
            ['skill',       'Systems Programming',          'Expert in C, C++, memory management, and low-level systems design.',                                                        ['C', 'C++', 'Systems', 'Algorithms'],   null],
        ],
        // student8 — Bianca Flores (CSE, DevOps)
        'student8' => [
            ['project',     'CI/CD Pipeline for Microservices', 'Designed a full CI/CD pipeline using GitHub Actions, Docker, and Kubernetes for a microservices architecture.',        ['Docker', 'Kubernetes', 'CI/CD', 'AWS'],'2024-07-10'],
            ['project',     'Infrastructure as Code Template', 'Terraform templates for provisioning a scalable AWS infrastructure with VPC, ECS, and RDS.',                            ['Terraform', 'AWS', 'IaC'],             '2024-04-20'],
            ['achievement', 'AWS Solutions Architect — Associate', 'Passed the AWS Certified Solutions Architect Associate exam.',                                                        ['AWS', 'Certification'],                '2024-03-01'],
            ['milestone',   'Internship at Cloud Startup',   'Completed a 3-month internship as a DevOps intern at a cloud-native startup.',                                             ['Internship', 'DevOps'],                '2023-10-15'],
            ['skill',       'Cloud & DevOps',                'Skilled in AWS, Docker, Kubernetes, Terraform, and GitHub Actions.',                                                       ['AWS', 'Docker', 'Kubernetes'],         null],
        ],
        // student9 — Marco Villanueva (CSE, security)
        'student9' => [
            ['project',     'CTF Challenge Platform',       'Built a Capture The Flag platform for the university cybersecurity club with 30+ challenges across web, crypto, and pwn.',  ['Python', 'Flask', 'Docker', 'CTF'],    '2024-05-01'],
            ['project',     'Network Intrusion Detection',  'ML-based network intrusion detection system using the KDD Cup dataset with 97% detection accuracy.',                        ['Python', 'ML', 'Networking'],          '2024-02-10'],
            ['achievement', 'National CTF — Top 10',        'Placed in the top 10 at the National Cybersecurity CTF competition.',                                                        ['CTF', 'Security', 'Award'],            '2024-04-15'],
            ['milestone',   'CEH Certification',            'Obtained the Certified Ethical Hacker (CEH) certification.',                                                                ['Security', 'Certification', 'CEH'],    '2023-12-01'],
            ['skill',       'Cybersecurity',                'Experienced in penetration testing, network security, digital forensics, and CTF challenges.',                              ['Security', 'Pentesting', 'Forensics'], null],
        ],
        // student10 — Isabelle Cruz (CSE, game dev)
        'student10' => [
            ['project',     'Procedural Dungeon Game',      'Indie roguelike game with procedurally generated dungeons built in Unity with C#.',                                          ['Unity', 'C#', 'Procedural', 'Game'],   '2024-06-01'],
            ['project',     'Shader Library for Unity',     'Open-source collection of custom HLSL shaders for stylized rendering effects.',                                             ['Unity', 'HLSL', 'Graphics', 'C#'],     '2024-01-15'],
            ['achievement', 'Global Game Jam Participant',  'Completed a full game in 48 hours at Global Game Jam 2024.',                                                                ['Game Jam', 'Unity'],                   '2024-01-28'],
            ['milestone',   '1,000 GitHub Stars',           'Open-source shader library reached 1,000 stars on GitHub.',                                                                ['Open Source', 'GitHub'],               '2024-05-10'],
            ['skill',       'Game Development',             'Proficient in Unity, C#, HLSL shaders, and procedural content generation.',                                                 ['Unity', 'C#', 'HLSL', 'Game Dev'],    null],
        ],
    ];

    public function run(): void
    {
        Portfolio::with('user')->get()->each(function (Portfolio $portfolio) {
            if ($portfolio->user->is_admin) return;
            // student1 is left blank — the logged-in user fills it themselves
            if ($portfolio->user->username === 'student1') {
                CustomizationSettings::create(array_merge(
                    CustomizationSettings::$defaults,
                    ['portfolio_id' => $portfolio->id]
                ));
                return;
            }

            $username = $portfolio->user->username;
            $items    = $this->studentItems[$username] ?? $this->defaultItems();

            foreach ($items as $order => [$type, $title, $desc, $tags, $date]) {
                PortfolioItem::create([
                    'portfolio_id'  => $portfolio->id,
                    'item_type'     => $type,
                    'title'         => $title,
                    'description'   => $desc,
                    'tags'          => $tags,
                    'item_date'     => $date,
                    'is_visible'    => true,
                    'display_order' => $order + 1,
                ]);
            }

            CustomizationSettings::create(array_merge(
                CustomizationSettings::$defaults,
                ['portfolio_id' => $portfolio->id]
            ));
        });
    }

    private function defaultItems(): array
    {
        return [
            ['project',     'E-Commerce Platform',    'Built a full-stack e-commerce platform with Laravel and Vue.js.',  ['Laravel', 'Vue.js', 'MySQL'],  '2024-01-01'],
            ['achievement', 'Dean\'s List 2024',       'Achieved Dean\'s List recognition for academic excellence.',        ['Academic'],                    '2024-06-01'],
            ['milestone',   'Internship Completed',    'Completed 6-month internship at a local tech company.',            ['Internship', 'Professional'],  '2023-12-01'],
            ['skill',       'Full-Stack Development',  'Proficient in PHP, Laravel, JavaScript, React, and MySQL.',        ['PHP', 'Laravel', 'React'],     null],
        ];
    }
}
