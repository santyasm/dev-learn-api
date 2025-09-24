<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 * schema="User",
 * title="User",
 * description="User model",
 * @OA\Property(
 * property="id",
 * type="string",
 * format="uuid",
 * description="User ID (UUID)"
 * ),
 * @OA\Property(
 * property="name",
 * type="string",
 * description="User's name",
 * example="John Doe"
 * ),
 * @OA\Property(
 * property="email",
 * type="string",
 * format="email",
 * description="User's email address",
 * example="john.doe@example.com"
 * ),
 * @OA\Property(
 * property="email_verified_at",
 * type="string",
 * format="date-time",
 * nullable=true,
 * description="Timestamp when email was verified",
 * example="2023-10-27T10:00:00.000000Z"
 * ),
 * @OA\Property(
 * property="role",
 * type="string",
 * description="User's role (e.g., 'student', 'admin', 'instructor')",
 * example="student",
 * enum={"student", "admin", "instructor"}
 * ),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the user was created",
 * example="2023-10-27T10:00:00.000000Z"
 * ),
 * @OA\Property(
 * property="updated_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the user was last updated",
 * example="2023-10-27T10:00:00.000000Z"
 * )
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
