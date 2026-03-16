<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private array $profiles = [
        // student1 — left blank so the logged-in user can fill it themselves
        1 => [
            'full_name' => 'Student 1',
            'bio'       => 'I am a BSIT student passionate about software development.',
            'program'   => 'BSIT',
            'contact_info' => [],
        ],
        2 => [
            'full_name' => 'Maria Santos',
            'bio'       => 'BSIT student specializing in web development and UI/UX design. I love building clean, accessible interfaces.',
            'program'   => 'BSIT',
            'contact_info' => [
                'github'   => 'https://github.com/mariasantos',
                'linkedin' => 'https://linkedin.com/in/mariasantos',
                'website'  => 'https://mariasantos.dev',
            ],
        ],
        3 => [
            'full_name' => 'Juan dela Cruz',
            'bio'       => 'Aspiring full-stack developer with a focus on Laravel and React. Currently building my capstone project on smart campus systems.',
            'program'   => 'BSIT',
            'contact_info' => [
                'github'    => 'https://github.com/juandelacruz',
                'linkedin'  => 'https://linkedin.com/in/juandelacruz',
                'instagram' => 'https://instagram.com/juandelacruz.dev',
            ],
        ],
        4 => [
            'full_name' => 'Angela Reyes',
            'bio'       => 'Database enthusiast and backend developer. Passionate about data engineering and cloud infrastructure.',
            'program'   => 'BSIT',
            'contact_info' => [
                'github'   => 'https://github.com/angelareyes',
                'linkedin' => 'https://linkedin.com/in/angelareyes',
                'twitter'  => 'https://x.com/angelareyes_dev',
            ],
        ],
        5 => [
            'full_name' => 'Carlo Mendoza',
            'bio'       => 'Mobile app developer focused on Flutter and Firebase. Dean\'s Lister and hackathon enthusiast.',
            'program'   => 'BSIT',
            'contact_info' => [
                'github'   => 'https://github.com/carlomendoza',
                'website'  => 'https://carlomendoza.io',
                'facebook' => 'https://facebook.com/carlo.mendoza.dev',
            ],
        ],
        6 => [
            'full_name' => 'Sofia Lim',
            'bio'       => 'CSE student with a deep interest in machine learning and computer vision. Research assistant at the AI lab.',
            'program'   => 'CSE',
            'contact_info' => [
                'github'   => 'https://github.com/sofialim',
                'linkedin' => 'https://linkedin.com/in/sofialim',
                'website'  => 'https://sofialim.tech',
            ],
        ],
        7 => [
            'full_name' => 'Rafael Torres',
            'bio'       => 'Systems programmer and competitive programmer. Interested in low-level computing, OS development, and algorithms.',
            'program'   => 'CSE',
            'contact_info' => [
                'github'   => 'https://github.com/rafaeltorres',
                'twitter'  => 'https://x.com/rafaeltorres_cs',
                'linkedin' => 'https://linkedin.com/in/rafaeltorres',
            ],
        ],
        8 => [
            'full_name' => 'Bianca Flores',
            'bio'       => 'DevOps and cloud computing enthusiast. AWS certified and passionate about CI/CD pipelines and containerization.',
            'program'   => 'CSE',
            'contact_info' => [
                'github'    => 'https://github.com/biancaflores',
                'linkedin'  => 'https://linkedin.com/in/biancaflores',
                'instagram' => 'https://instagram.com/bianca.in.tech',
            ],
        ],
        9 => [
            'full_name' => 'Marco Villanueva',
            'bio'       => 'Cybersecurity student and CTF player. Interested in ethical hacking, network security, and digital forensics.',
            'program'   => 'CSE',
            'contact_info' => [
                'github'  => 'https://github.com/marcovillanueva',
                'twitter' => 'https://x.com/marco_sec',
                'website' => 'https://marcovillanueva.net',
            ],
        ],
        10 => [
            'full_name' => 'Isabelle Cruz',
            'bio'       => 'Game developer and graphics programmer. Building indie games with Unity and exploring procedural generation.',
            'program'   => 'CSE',
            'contact_info' => [
                'github'    => 'https://github.com/isabellecruz',
                'instagram' => 'https://instagram.com/isabelle.makes.games',
                'website'   => 'https://isabellecruz.games',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->profiles as $n => $profile) {
            $user = User::create([
                'name'         => $profile['full_name'],
                'full_name'    => $profile['full_name'],
                'email'        => "student{$n}@example.com",
                'username'     => "student{$n}",
                'program'      => $profile['program'],
                'bio'          => $profile['bio'],
                'contact_info' => $profile['contact_info'],
                'is_verified'  => true,
                'password'     => 'Password1!',
            ]);

            Portfolio::create(['user_id' => $user->id, 'is_public' => true]);
        }

        // Admin
        $admin = User::create([
            'name'        => 'Admin User',
            'full_name'   => 'Admin User',
            'email'       => 'admin@example.com',
            'username'    => 'admin',
            'program'     => 'BSIT',
            'is_verified' => true,
            'is_admin'    => true,
            'password'    => 'Password1!',
        ]);
        Portfolio::create(['user_id' => $admin->id]);
    }
}
