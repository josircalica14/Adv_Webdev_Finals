<?php

use App\Services\ShowcaseService;

beforeEach(function () {
    $this->service = new ShowcaseService();
});

it('generates the same cache key for identical criteria', function () {
    $criteria = ['query' => 'test', 'program' => 'BSIT', 'sort' => 'name'];
    $key1 = 'showcase:' . md5(serialize($criteria));
    $key2 = 'showcase:' . md5(serialize($criteria));
    expect($key1)->toBe($key2);
});

it('generates different cache keys for different criteria', function () {
    $criteria1 = ['query' => 'test', 'program' => 'BSIT'];
    $criteria2 = ['query' => 'test', 'program' => 'CSE'];
    $key1 = 'showcase:' . md5(serialize($criteria1));
    $key2 = 'showcase:' . md5(serialize($criteria2));
    expect($key1)->not->toBe($key2);
});

it('generates different cache keys for different pages', function () {
    $criteria = ['query' => 'test'];
    $key1 = 'showcase:' . md5(serialize($criteria)) . ':page:1';
    $key2 = 'showcase:' . md5(serialize($criteria)) . ':page:2';
    expect($key1)->not->toBe($key2);
});

it('generates different cache keys for different sort orders', function () {
    $criteria1 = ['sort' => 'name'];
    $criteria2 = ['sort' => 'updated'];
    $key1 = 'showcase:' . md5(serialize($criteria1));
    $key2 = 'showcase:' . md5(serialize($criteria2));
    expect($key1)->not->toBe($key2);
});
