<?php

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

$URL = 'api/v1/auth/register';
$faker = Faker::create();

it('requires a name', function () use ($URL) {
    $response = $this->postJson($URL, [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('name field max 255 length', function () use ($URL, $faker) {
    $userData = [
        'name' => $faker->sentence(256), // Generate a name longer than 255 characters
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.name')
                ->where('errors.name.0', 'The name field must not be greater than 255 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'name' => $userData['name'],
    ]);
});

it('name field 3 min length', function () use ($URL, $faker) {
    $userData = [
        'name' => $faker->text(5),
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.name')
                ->where('errors.name.0', 'The name field must be at least 6 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'name' => $userData['name'],
    ]);
});

it('requires a valid email', function () use ($URL, $faker) {
    $response = $this->postJson($URL, [
        'name' => $faker->name,
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('email');
});

it('validates user email unique', function () use ($URL, $faker) {

    $user_email = User::factory()->create()->email;

    $userData = [
        'name' => $faker->name(),
        'email' => $user_email,
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.email')
                ->where('errors.email.0', 'The email has already been taken.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'name' => $userData['name'],
    ]);
});

it('user password required', function () use ($URL, $faker) {
    $userData = [
        'name' => $faker->name(),
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

it('requires a password confirmation', function () use ($URL, $faker) {
    $response = $this->postJson($URL, [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => 'password',
        'password_confirmation' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

it('user password max 255', function () use ($URL, $faker) {
    $password = str_repeat('a', 256);

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
                ->where('errors.password.0', 'The password field must not be greater than 255 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('users', [
        'email' => $userData['email'],
    ]);
});

it('validates user password min 6', function () use ($URL, $faker) {
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

it('can register a user', function () use ($URL, $faker) {
    $response = $this->postJson($URL, [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson(
            fn (AssertableJson $json) => $json->where('message', 'Usuário cadastrado com sucesso')
                ->etc()
        );
});
