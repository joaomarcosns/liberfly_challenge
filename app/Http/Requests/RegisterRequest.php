<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   @OA\Property(property="name", type="string", example="John Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *   @OA\Property(property="password", type="string", example="123456"),
 *   @OA\Property(property="password_confirmation", type="string", example="123456")
 * )
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', 'min:6'],
            'email' => ['email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'max:255', 'min:6'],
        ];
    }
}
