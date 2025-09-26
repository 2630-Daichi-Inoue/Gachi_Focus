<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    const ADMIN_ROLE_ID = 1;
    const USER_ROLE_ID = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    # USER - POST 
    # a user can have many posts
    public function posts()
    {
        return $this->hasMany(Post::class);     
    }

    # USER - FOLLOW
    # a user has many followers
    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    # USER - FOLLOW
    # a user follows many other users
    # to get all the users that the user is following
    public function following()
    {
        return $this->hasMany(Follow::class,'follower_id');
    }

    # return TRUE if the user is following the given user
    public function isFollowed()
    {
        return $this->followers()->where('follower_id', Auth::user()->id)->exists();
        // $this->followers() - get all the followers of the user
        // ->where('follower_id', Auth::user()->id) - check if the follower_id of the follower is the same as the logged in user
        // ->exists() - check if there is any follower with the follower_id of the logged in user
    }
}
