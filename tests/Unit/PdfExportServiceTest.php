<?php

use App\Models\PortfolioItem;
use Illuminate\Support\Collection;

it('groups portfolio items correctly by type', function () {
    $items = collect([
        new PortfolioItem(['item_type' => 'project', 'title' => 'Project A']),
        new PortfolioItem(['item_type' => 'project', 'title' => 'Project B']),
        new PortfolioItem(['item_type' => 'achievement', 'title' => 'Award']),
        new PortfolioItem(['item_type' => 'skill', 'title' => 'PHP']),
    ]);

    $grouped = $items->groupBy('item_type');

    expect($grouped)->toHaveKey('project')
        ->and($grouped['project'])->toHaveCount(2)
        ->and($grouped)->toHaveKey('achievement')
        ->and($grouped['achievement'])->toHaveCount(1)
        ->and($grouped)->toHaveKey('skill')
        ->and($grouped['skill'])->toHaveCount(1);
});

it('returns empty groups when no items exist', function () {
    $items = collect([]);
    $grouped = $items->groupBy('item_type');
    expect($grouped)->toBeEmpty();
});

it('handles all item types', function () {
    $types = ['project', 'achievement', 'skill', 'experience', 'education'];
    $items = collect(array_map(fn($t) => new PortfolioItem(['item_type' => $t, 'title' => $t]), $types));
    $grouped = $items->groupBy('item_type');
    expect($grouped)->toHaveCount(5);
    foreach ($types as $type) {
        expect($grouped)->toHaveKey($type);
    }
});
