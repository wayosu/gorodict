<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasUuids;

    protected $table = 'articles';

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'content',
        'article_category_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            $article->slug = Str::slug($article->title);
        });

        static::deleting(function ($article) {
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            // hapus gambar dari content jika ada
            preg_match_all('/<img[^>]+src="([^">]+)"/', $article->content, $matches);
            foreach ($matches[1] as $src) {
                if (Str::startsWith($src, '/storage/')) {
                    $path = str_replace('/storage/', '', $src);
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    // Accessor untuk URL thumbnail
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return Storage::url($this->thumbnail);
        }
        return null;
    }

    // Accessor untuk content dengan URL gambar yang benar
    public function getContentAttribute($value)
    {
        return str_replace(
            'src="article-content/',
            'src="' . Storage::url('article-content/'),
            $value
        );
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag_pivot', 'article_id', 'article_tag_id');
    }
}
