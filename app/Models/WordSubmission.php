<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WordSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'word_gorontalo',
        'word_indonesia',
        'category_id',
        'description',
        'audio_path',
        'status',
        'rejection_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
