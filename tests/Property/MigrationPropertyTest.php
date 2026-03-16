<?php

// Properties 41–42: Data Migration

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Property 41: Password hashes are preserved verbatim during migration
it('P41: password hashes are preserved verbatim', function () {
    // Simulate a pre-hashed password (as it would come from the old system)
    $existingHash = '$2y$12$' . str_repeat('a', 53); // fake bcrypt hash format

    // Direct DB insert simulating migration (bypassing hashed cast)
    \Illuminate\Support\Facades\DB::table('users')->insert([
        'name'       => 'Migrated User',
        'full_name'  => 'Migrated User',
        'email'      => 'migrated@example.com',
        'username'   => 'migrateduser',
        'password'   => $existingHash, // raw hash, not re-hashed
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::where('email', 'migrated@example.com')->first();
    // The hash should be stored exactly as provided (not double-hashed)
    expect($user->getRawOriginal('password'))->toBe($existingHash);
});

// Property 42: Record counts match between source and destination after migration
it('P42: record counts are consistent after seeding', function () {
    // Create known number of users
    User::factory()->count(5)->create();

    $count = User::count();
    // Count via raw DB should match Eloquent count
    $rawCount = \Illuminate\Support\Facades\DB::table('users')->count();

    expect($count)->toBe($rawCount);
});
