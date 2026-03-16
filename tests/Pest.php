<?php

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Property');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');
