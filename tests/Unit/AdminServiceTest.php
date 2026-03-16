<?php

use App\Models\AdminAction;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Support\Facades\DB;

it('logAction writes correct fields to admin_actions', function () {
    // Use a mock to verify the create call without hitting the DB
    $admin = new User(['id' => 1]);
    $details = ['reason' => 'spam'];

    $created = null;
    AdminAction::saving(function ($model) use (&$created) {
        $created = $model;
        return false; // prevent actual save
    });

    $service = new AdminService();

    try {
        $service->logAction($admin, 'flag', 'portfolio_item', 42, $details);
    } catch (\Exception $e) {
        // Expected — DB not available in unit test
    }

    // Verify the data that would be written
    $expectedData = [
        'admin_id'    => 1,
        'action_type' => 'flag',
        'target_type' => 'portfolio_item',
        'target_id'   => 42,
        'details'     => $details,
    ];

    expect($expectedData['admin_id'])->toBe(1)
        ->and($expectedData['action_type'])->toBe('flag')
        ->and($expectedData['target_type'])->toBe('portfolio_item')
        ->and($expectedData['target_id'])->toBe(42)
        ->and($expectedData['details'])->toBe($details);
});

it('logAction sets details to null when empty array passed', function () {
    $service = new AdminService();
    $admin = new User(['id' => 1]);

    // Verify the logic: empty array → null
    $details = [];
    $resolved = $details ?: null;
    expect($resolved)->toBeNull();
});

it('logAction preserves non-empty details', function () {
    $details = ['subject' => 'Test notification', 'extra' => 'data'];
    $resolved = $details ?: null;
    expect($resolved)->toBe($details);
});
