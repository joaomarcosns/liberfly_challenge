<?php

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

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

it('requires a title and description', function () use ($URL) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $response = $this->postJson($URL, [
        'title' => '',
        'description' => '',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('title');
});

it('title field max 255 length', function () use ($URL, $faker) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $title = str_repeat('a', 256);
    $userData = [
        'title' => $title,
        'description' => $faker->text(256),
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.title')
                ->where('errors.title.0', 'The title field must not be greater than 255 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('posts', [
        'title' => $userData['title'],
    ]);
});

it('title field 3 min length', function () use ($URL, $faker) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $title = str_repeat('a', 2);

    $userData = [
        'title' => $title,
        'description' => $faker->text(256),
    ];

    $response = $this->postJson($URL, $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.title')
                ->where('errors.title.0', 'The title field must be at least 3 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('posts', [
        'title' => $userData['title'],
    ]);
});

it('returns post successfully', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Cria um post
    $post = Post::factory()->create();

    // Faz uma requisição GET para o endpoint show
    $response = $this->getJson(route('posts.show', ['post_id' => $post->id]));

    // Verifica se a resposta está correta
    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'data',
        ]);
});

it('returns error when post not found', function () {

    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Cria um post fake com um ID que você pode garantir que não existe
    $nonExistentPostId = 9999;

    // Faz uma requisição GET para o ID que não existe
    $response = $this->getJson(route('posts.show', ['post_id' => $nonExistentPostId]));

    // Verifica se a resposta está correta
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'error' => 'Post not found',
        ]);

    // Verifica se o campo 'data' não está presente no JSON de resposta
    $response->assertJsonMissing(['data']);
});

it('should publish a post', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);
    // Cria um post não publicado
    $post = Post::factory()->create(['status' => PostStatusEnum::DRAFT]);

    // Chama o endpoint da API para publicar o post
    $response = $this->patchJson(route('posts.published', ['post_id' => $post->id]));

    // Verifica se a resposta está correta
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson(['message' => 'Post published']);

    // Verifica se o post foi atualizado corretamente no banco de dados
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'status' => PostStatusEnum::PUBLISHED,
    ]);
});

it('should return error if post is already published', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Cria um post já publicado
    $post = Post::factory()->create(['status' => PostStatusEnum::PUBLISHED]);

    // Chama o endpoint da API para publicar o post
    $response = $this->patchJson(route('posts.published', ['post_id' => $post->id]));

    // Verifica se a resposta está correta
    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson(['message' => 'Post already published']);
});

it('should return error if post does not exist', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);
    // Chama o endpoint da API para publicar um post que não existe
    $response = $this->patchJson(route('posts.published', ['post_id' => 999]));

    // Verifica se a resposta está correta
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson(['error' => 'Post not found']);
});

it('should archive a post', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);
    // Cria um post não publicado
    $post = Post::factory()->create(['status' => PostStatusEnum::DRAFT]);
    $response = $this->patchJson(route('posts.archive', ['post_id' => $post->id]));

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Post archived',
        ]);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'status' => PostStatusEnum::ARCHIVED,
    ]);
});

it('should return error if post is already archived', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $post = Post::factory()->create(['status' => PostStatusEnum::ARCHIVED]);
    $response = $this->patchJson(route('posts.archive', ['post_id' => $post->id]));

    $response->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJson([
            'error' => 'Post already archived',
        ]);
});

it('requires a title and description in update post ', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $post = Post::factory()->create(['user_id' => $user->id]);

    $userData = [
        'title' => '',
        'description' => '',
    ];

    $response = $this->putJson(route('posts.update', ['post_id' => $post->id]), $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors('title');
});

it('title field max 255 length in update post', function () use ($faker) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $title = str_repeat('a', 256);

    $post = Post::factory()->create(['user_id' => $user->id]);

    $userData = [
        'title' => $title,
        'description' => $faker->text(256),
    ];

    $response = $this->putJson(route('posts.update', ['post_id' => $post->id]), $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.title')
                ->where('errors.title.0', 'The title field must not be greater than 255 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('posts', [
        'title' => $userData['title'],
    ]);
});

it('title field 3 min length in update post', function () use ($faker) {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $title = str_repeat('a', 2);

    $post = Post::factory()->create(['user_id' => $user->id]);

    $userData = [
        'title' => $title,
        'description' => $faker->text(256),
    ];

    $response = $this->putJson(route('posts.update', ['post_id' => $post->id]), $userData);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson(
            fn (AssertableJson $json) => $json->has('errors.title')
                ->where('errors.title.0', 'The title field must be at least 3 characters.')
                ->etc()
        );

    $this->assertDatabaseMissing('posts', [
        'title' => $userData['title'],
    ]);
});

it('updates a post different user', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    $user2 = User::factory()->create();

    // Criar um post associado ao usuário
    $post = Post::factory()->create(['user_id' => $user2->id]);

    // Simular requisição para atualizar o post
    $response = $this->putJson(route('posts.update', ['post_id' => $post->id]), [
        'title' => 'Novo título do post',
        'description' => 'Novo conteúdo do post',
    ]);

    // Verificar se a resposta está correta
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'error' => 'Post not found',
        ]);
});

it('updates a post successfully', function () {
    // Criar um usuário para autenticar
    $user = User::factory()->create();

    // Autenticar o usuário usando Sanctum
    Sanctum::actingAs($user);

    // Criar um post associado ao usuário
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Simular requisição para atualizar o post
    $response = $this->putJson(route('posts.update', ['post_id' => $post->id]), [
        'title' => 'Novo título do post',
        'description' => 'Novo conteúdo do post',
    ]);

    // Verificar se a resposta está correta
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'message' => 'Post update',
        ]);

    // Verificar se o post foi atualizado no banco de dados
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Novo título do post',
        'description' => 'Novo conteúdo do post',
    ]);
});
