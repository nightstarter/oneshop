<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'checksum',
        'width',
        'height',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_images')
            ->withPivot(['sort_order', 'alt', 'is_primary']);
    }
}