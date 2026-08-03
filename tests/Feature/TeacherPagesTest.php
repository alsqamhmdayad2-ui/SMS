<?php

use App\Models\User;

test('teacher root redirects to dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/teacher');

    $response->assertRedirect('/teacher/dashboard');
});

test('teacher pages render successfully', function () {
    $user = User::factory()->create();

    foreach (['/teacher/dashboard', '/teacher/students', '/teacher/schedule', '/teacher/grades', '/teacher/profile'] as $uri) {
        $response = $this
            ->actingAs($user)
            ->get($uri);

        $response->assertOk();
    }
});
