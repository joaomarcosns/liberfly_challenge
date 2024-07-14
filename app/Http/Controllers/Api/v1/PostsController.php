<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;

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
        ]);
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
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
