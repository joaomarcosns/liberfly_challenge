<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *      schema="Post",
 *
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string",
 *          example="Example Post"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *          example="John Doe"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *          example="published"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          type="integer",
 *          example=1
 *      ),
 *      @OA\Property(
 *          property="published_at",
 *          type="string",
 *          format="date-time",
 *          example="2024-07-15T12:00:00Z"
 *      ),
 *      @OA\Property(
 *          property="user",
 *          type="object",
 *          ref="#/components/schemas/User"
 *      )
 * )
 */
class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatusEnum::class,
        ];
    }

    /**
     * Scope para buscar posts com status 'published'.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
