<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'address'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
