<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{


    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        $user = User::firstWhere('email', $credentials['email']);
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => "Invalid username or password"
            ], Response::HTTP_BAD_REQUEST);
        }

        // Delete old tokens
        $user->tokens()->delete();

        $expiresAt = now()->addWeek();
        $token = $user->createToken(name: 'user-token', expiresAt: $expiresAt)->plainTextToken;

        return response()->json([
            'data' =>  $user,
            'token' =>  $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email has already been taken."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email has already been taken."}})
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        User::query()->create($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
        ], Response::HTTP_CREATED);
    }
}
