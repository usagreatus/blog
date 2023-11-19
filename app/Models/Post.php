<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, fn($query, $search) =>
            $query->where(fn($query) =>
                $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('body', 'like', '%' . $search . '%')
            )
        );

        // a search using where exists
        // SELECT * FROM `posts` WHERE EXISTS (SELECT * FROM `categories` WHERE `categories`.`id` = `posts`.`category_id` and `categories`.`slug` = 'cumque-rerum-enim-excepturi-rerum-assumenda') ORDER BY `created_at` DESC
        // $query->when($filters['category'] ?? false, fn($query, $category) =>
        //   $query
        //     ->whereExists(fn($query) =>
        //    $query->from('categories')->whereColumn('categories.id', 'posts.category_id')
        //    ->where('categories.slug', $category))
        // );

        // a search using where has, in mysql it will be same query than previous
        $query->when($filters['category'] ?? false, fn($query, $category) =>
            $query->whereHas('category', fn($query) =>
                $query->where('slug', $category)
            )
        );

        $query->when($filters['author'] ?? false, fn($query, $author) =>
            $query->whereHas('author', fn($query) =>
                $query->where('username', $author)
            )
        );
    }

    // every post query will load category and author
    //protected $with = ['category', 'author'];

    //protected $fillable = [
    //    'title',
    //    'excerpt',
    //    'body'
    //];

    /**
     * alternative option for route model binding
     */
    //public function getRouteKeyName()
    //{
    //    return 'slug';
    //}

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
