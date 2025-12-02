<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Generate slug from name
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($category) {
    //         if (empty($category->slug)) {
    //             $category->slug = \Str::slug($category->name);
    //         }
    //     });

    //     static::updating(function ($category) {
    //         if ($category->isDirty('name') && empty($category->slug)) {
    //             $category->slug = \Str::slug($category->name);
    //         }
    //     });
    // }
    protected static function boot()
{
    parent::boot();

    static::creating(function ($category) {
        // Always generate slug, even if one is provided (ensures consistency)
        $category->slug = Str::slug($category->name);
    });

    static::updating(function ($category) {
        if ($category->isDirty('name')) {
            $category->slug = Str::slug($category->name);
        }
    });
}
}