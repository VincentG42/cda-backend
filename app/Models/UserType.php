<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    use HasFactory;

    public const PLAYER = 'player';
    public const COACH = 'coach';
    public const STAFF = 'staff';
    public const PRESIDENT = 'president';
    public const ADMIN = 'admin';

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
