<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\CausesActivity;

class User extends Authenticatable
{
    use CausesActivity;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
