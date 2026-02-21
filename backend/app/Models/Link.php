<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'url',
        'title',
        'description',
        'image',
        'site_name',
        'favicon',
        'notes',
        'status',
        'is_favourite',
        'last_opened_at'
    ];

    protected $casts = [
        'is_favourite' => 'boolean',
        'last_opened_at' => 'datetime'
    ];

    public function user(): BelongsTo

    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
