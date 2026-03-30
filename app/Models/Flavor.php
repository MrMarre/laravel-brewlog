<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flavor extends Model
{
    protected $guarded = [];

    public function logs()
    {
        return $this->belongsToMany(Log::class, 'log_flavors')
            ->withPivot('type');
    }
}
