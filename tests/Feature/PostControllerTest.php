<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(RefreshDatabase::class);

$URL = 'api/v1/posts';
$faker = Faker::create();

it('returns published posts with user information', function () use ($URL) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Criar um post associado ao usuário
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title' => 'Iure velit ducimus quo enim excepturi.',
        'description' => 'Ad a eligendi earum similique. Rerum maxime aut voluptas. Nihil mollitia aut atque est.',
        'status' => 'published',
        'published_at' => now(),
    ]);

    // Fazer a requisição para o endpoint autenticado
    $response = $this->getJson($URL);

    // Verificar se a resposta contém os dados esperados
    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'published_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});
