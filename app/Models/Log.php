<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'brand_name',
        'product_name',
        'brew_method',
        'coffee_weight',
        'water_weight',
        'bloom_time',
        'brew_time',
        
    ];

    public function listedFlavors()
    {
        return $this->belongsToMany(Flavor::class, 'log_flavors')
            ->withPivot('type')
            ->wherePivot('type', 'listed');
    }

    public function tastedFlavors()
    {
        return $this->belongsToMany(Flavor::class, 'log_flavors')
            ->withPivot('type')
            ->wherePivot('type', 'tasted');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
}
