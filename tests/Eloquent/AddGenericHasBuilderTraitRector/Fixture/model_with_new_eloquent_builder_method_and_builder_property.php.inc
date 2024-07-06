<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\FooBuilder;
use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected static string $builder = FooBuilder::class;

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}

?>
-----
<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\FooBuilder;
use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\HasBuilder<\App\Models\Builders\UserBuilder<$this>>
     */
    use \Illuminate\Database\Eloquent\HasBuilder;
    protected static string $builder = FooBuilder::class;

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }
}

?>
