<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login()
    {
    }

    public function register(RegisterRequest $request)
    {
        $user = User::query()->create($request->validated());
        return response()->json([
            'message' => "Usuário cadastrado com sucesso"
        ], Response::HTTP_CREATED);
    }
}
