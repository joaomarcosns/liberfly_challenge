<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\PostStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 */
class PostsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/posts",
     *      operationId="getPostsList",
     *      tags={"Posts"},
     *      summary="Get list of published posts",
     *      description="Returns a list of published posts with basic user information",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Post")
     *      ),
     *      security={{"bearerAuth": {}}}
     * )
     */
    public function index()
    {
        $posts = Post::with(['user:id,name,email'])->published()->get();

        return response()->json([
            'data' => $posts
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Store a new post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StorePostRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function store(StorePostRequest $request)
    {
        Post::query()->create($request->validated());

        return response()->json([
            'message' => 'Post created successfully'
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posts/{post_id}",
     *     tags={"Posts"},
     *     summary="Get a specific post",
     *     description="Returns a specific post by ID",
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Post not found")
     *         )
     *     ),
     *      security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function show(int $post_id)
    {
        try {
            $post = Post::with(['user:id,name,email'])->findOrFail($post_id);
            return response()->json([
                'data' => $post
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Post not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, $post_id)
    {
        try {
            $user = auth()->user();
            $post = Post::findOrFail($post_id);

            if ($post->user_id !=  $user->id) {
                return response()->json([
                    'message' => 'Post not found'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $post->update($request->validated());

            return response()->json([
                'message' => 'Post update'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Post not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to publish post'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/posts/{post_id}/published",
     *     summary="Publish a post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to publish",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post successfully published",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Post already published",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post already published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Post not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to publish post",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to publish post")
     *         )
     *     ),
     *      security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function published(int $post_id)
    {
        try {
            $post = Post::findOrFail($post_id);

            if ($post->status === PostStatusEnum::PUBLISHED) {
                return response()->json([
                    'message' => 'Post already published'
                ], Response::HTTP_BAD_REQUEST);
            }

            $post->update([
                'status' => PostStatusEnum::PUBLISHED,
                'published_at' => now()
            ]);

            return response()->json([
                'message' => 'Post published'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Post not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to publish post'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Archive a post by its ID.
     *
     * @param int $post_id ID of the post to be archived
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Patch(
     *      path="/api/v1/posts/{post_id}/archive",
     *      tags={"Posts"},
     *      summary="Archive a post by its ID",
     *      operationId="archivePost",
     *      @OA\Parameter(
     *          name="post_id",
     *          in="path",
     *          required=true,
     *          description="ID of the post to archive",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Post archived successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Post archived")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Post already archived",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Post already archived")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Post not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Post not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Failed to archive post",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Failed to archive post")
     *          )
     *      ),
     *      security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function archive(int $post_id)
    {
        try {
            $post = Post::findOrFail($post_id);

            if ($post->status === PostStatusEnum::ARCHIVED) {
                return response()->json([
                    'error' => 'Post already archived'
                ], Response::HTTP_BAD_REQUEST);
            }

            $post->update([
                'status' => PostStatusEnum::ARCHIVED,
            ]);

            return response()->json([
                'message' => 'Post archived'
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Post not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to archived post'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
