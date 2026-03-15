<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    // This allows Laravel to "fill" these columns during ingestion
    protected $fillable = ['filename', 'path', 'type'];

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }
}