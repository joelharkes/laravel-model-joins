<?php

namespace Joelharkes\LaravelModelJoins\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JoinsModelsTest extends TestCase
{
    public function testSimpleHasMany()
    {
        $blog = new Blog();
        $query = $blog->newQuery()->joinMany(Comment::class)->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" on "comments"."blog_id" = "blogs"."id"', $query);
    }

    public function testSimpleHasManyForQueryBuilder()
    {
        $blog = new Blog();
        $query = $blog->newQuery()->joinMany(Comment::query())->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" on "comments"."blog_id" = "blogs"."id"', $query);
    }

    public function testSimpleHasManyForRelation()
    {
        $blog = new Blog();
        $blog->id = 123;
        $query = $blog->newQuery()->joinMany($blog->comments())->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" on "comments"."blog_id" = "blogs"."id"', $query);
    }

    public function testJoinRelation()
    {
        $blog = new Blog();
        $blog->id = 123;
        $query = $blog->newQuery()->joinRelation('comments')->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" on "comments"."blog_id" = "blogs"."id"', $query);
    }

    public function testJoinRelationWithAlias()
    {
        $blog = new Blog();
        $blog->id = 123;
        $query = $blog->newQuery()->joinRelation('notes', aliasAsRelations: true)->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" as "notes" on "notes"."blog_id" = "blogs"."id"', $query);
    }

    public function testJoinOneRelationWith()
    {
        $blog = new Blog();
        $query = (new Comment())->newQuery()->joinRelation('blog')->toSql();
        $this->assertSame('select * from "comments" inner join "blogs" on "blogs"."id" = "comments"."blog_id"', $query);
    }

    public function testSimpleHasOne()
    {
        $query = (new Comment())->newQuery()->joinOne(Blog::class)->toSql();
        $this->assertSame('select * from "comments" inner join "blogs" on "blogs"."id" = "comments"."blog_id"', $query);
    }

    public function testSimpleHasManyAlternativePrimaryKeyName()
    {
        $blog = new Alternative();
        $query = $blog->newQuery()->joinMany(Blog::class)->toSql();
        $this->assertSame('select * from "alternatives" inner join "blogs" on "blogs"."alternative_key" = "alternatives"."key"', $query);
    }

    public function testIncludeScopesInJoin()
    {
        $blog = new Blog();
        $query = $blog->newQuery()->joinMany(DeletableComment::class)->toSql();
        $this->assertSame('select * from "blogs" inner join "deletable_comments" on "deletable_comments"."blog_id" = "blogs"."id" and ("deletable_comments"."deleted_at" is null)', $query);
    }

    public function testCanJoinBuilder()
    {
        $blog = new Blog();
        $query = $blog->newQuery()->joinMany(DeletableComment::withTrashed())->toSql();
        $this->assertSame('select * from "blogs" inner join "deletable_comments" on "deletable_comments"."blog_id" = "blogs"."id"', $query);
    }

    public function testAddWhereStatements()
    {
        $blog = new Blog();
        $query = $blog->newQuery()->joinMany(Comment::query()->whereNull('comments.deleted_at'))->toSql();
        $this->assertSame('select * from "blogs" inner join "comments" on "comments"."blog_id" = "blogs"."id" and ("comments"."deleted_at" is null)', $query);
    }

    public function testAddingOnRelation()
    {
        $blog = new Blog();
        $query = $blog->comments()->joinOne(User::class)->toSql();
        $this->assertSame('select * from "comments" inner join "users" on "users"."id" = "comments"."user_id" where "comments"."blog_id" is null and "comments"."blog_id" is not null', $query);
    }
}

class Blog extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function notes()
    {
        return $this->hasMany(Comment::class);
    }
}
class Comment extends Model
{
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
class User extends Model
{
}

class DeletableComment extends Model
{
    use SoftDeletes;
}

class Alternative extends Model
{
    protected $primaryKey = 'key';
}
