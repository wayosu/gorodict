<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Word extends Model
{
    use HasUuids;

    protected $fillable = [
        'word_gorontalo',
        'word_indonesia',
        'category_id',
        'description',
        'audio_path'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Accessor untuk URL audio
    public function getAudioUrlAttribute()
    {
        return $this->audio_path ? Storage::url($this->audio_path) : null;
    }

    // Method untuk menghapus file audio saat record dihapus
    protected static function booted()
    {
        static::deleting(function ($word) {
            if ($word->audio_path) {
                Storage::disk('public')->delete($word->audio_path);
                Storage::delete($word->audio_path);
            }
        });

        // Menghapus file audio lama saat update file baru
        static::updating(function ($word) {
            if ($word->isDirty('audio_path') && $word->getOriginal('audio_path')) {
                Storage::disk('public')->delete($word->getOriginal('audio_path'));
                Storage::delete($word->getOriginal('audio_path'));
            }
        });
    }
}
