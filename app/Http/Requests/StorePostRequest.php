<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StorePostRequest",
 *     title="Store Post Request",
 *     required={"title", "content", "status"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Example Title",
 *         description="The title of the post"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
 *         description="The content of the post"
 *     )
 * )
 */
class StorePostRequest extends FormRequest
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
            'title' => ['required', 'max:255', 'min:3'],
            'description' => ['required']
        ];
    }
}
