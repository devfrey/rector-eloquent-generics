<?php

namespace Tests\Eloquent\DocumentRelationGenericsRector\Fixture;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followers()
    {
        return $this->hasMany(self::class, 'followers');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->using(UserTeam::class)
            ->as('user_team')
            ->withPivot([
                'role',
            ]);
    }
}

class Post extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class Image extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Team extends Model
{
    //
}

class UserTeam extends Pivot
{
    //
}

?>
-----
<?php

namespace Tests\Eloquent\DocumentRelationGenericsRector\Fixture;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class User extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Eloquent\DocumentRelationGenericsRector\Fixture\Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Eloquent\DocumentRelationGenericsRector\Fixture\User, $this>
     */
    public function followers()
    {
        return $this->hasMany(self::class, 'followers');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Tests\Eloquent\DocumentRelationGenericsRector\Fixture\Team, $this>
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class)
            ->using(UserTeam::class)
            ->as('user_team')
            ->withPivot([
                'role',
            ]);
    }
}

class Post extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Tests\Eloquent\DocumentRelationGenericsRector\Fixture\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Tests\Eloquent\DocumentRelationGenericsRector\Fixture\Image, $this>
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class Image extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Team extends Model
{
    //
}

class UserTeam extends Pivot
{
    //
}

?>
