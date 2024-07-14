<?php

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

$URL = 'api/v1/auth/login';
$faker = Faker::create();

it('email max 255', function () use ($URL) {
    $email = str_repeat('a', 256) . '@example.com';
    $response = $this->postJson($URL, [
        'email' => $email,
        'password' => 'password',
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('email');
});

it('email min 10', function () use ($URL) {
    $email = 'a@b.c';
    $response = $this->postJson($URL, [
        'email' => $email,
        'password' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('email');
});

it('requires a valid email', function () use ($URL, $faker) {
    $response = $this->postJson($URL, [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('email');
});

it('password required', function () use ($URL, $faker) {
    $userData = [
        'email' => $faker->email(),
        'password' => '',
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.password')
                ->where('errors.password.0', 'The password field is required.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'password' => $userData['password'],
    ]);
});

it('password min 6', function () use ($URL, $faker) {
    $password = str_repeat('a', 5);

    $userData = [
        'name' => $faker->name(),
        'email' => $faker->email(),
        'password' => $password,
        'password_confirmation' => $password,
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.password')
                ->where('errors.password.0', 'The password field must be at least 6 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'password' => $userData['password'],
    ]);
});

it('should authenticate user and return token', function () use ($URL, $faker) {
    $password = $faker->password(11);

    $user = User::factory()->create([
        'email' => $faker->safeEmail(),
        'password' => $password
    ]);

    $response = $this->postJson($URL, [
        'email' => $user->email,
        'password' => $password,
    ]);


    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ],
            'token',
        ]);
});

it('should return error for invalid credentials', function () use ($URL, $faker) {
    $response = $this->postJson($URL, [
        'email' => $faker->safeEmail(),
        'password' => 'invalidpassword',
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['message' => 'Invalid username or password']);
});
