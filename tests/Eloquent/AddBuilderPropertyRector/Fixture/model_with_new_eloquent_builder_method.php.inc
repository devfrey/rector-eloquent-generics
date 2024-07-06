<?php

namespace Tests\Eloquent\AddBuilderPropertyRector\Fixture;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['email'];

    public function foo(): BelongsTo
    {
        return $this->belongsTo(Foo::class);
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}

?>
-----
<?php

namespace Tests\Eloquent\AddBuilderPropertyRector\Fixture;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected static string $builder = \App\Models\Builders\UserBuilder::class;

    protected $fillable = ['email'];

    public function foo(): BelongsTo
    {
        return $this->belongsTo(Foo::class);
    }
}

?>
