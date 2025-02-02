<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WordSubmission extends Model
{
    use HasUuids;

    protected $table = 'word_submissions';

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            $submission->user_id = auth()->id();
            $submission->status = 'pending';
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
