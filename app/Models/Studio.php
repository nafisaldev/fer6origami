<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Studio extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'country',
        'founded_year',
        'website',
    ];

    protected $casts = [
        'founded_year' => 'integer',
    ];

    public function animes(): HasMany
    {
        return $this->hasMany(Anime::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if (blank($this->logo_path)) {
            return asset('images/poster-placeholder.svg');
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://'])) {
            return $this->logo_path;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
