<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function newEloquentBuilder($query)
    {
        return new UserBuilder($query);
    }
}

?>
-----
<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function newEloquentBuilder($query)
    {
        return new UserBuilder($query);
    }
}

?>
