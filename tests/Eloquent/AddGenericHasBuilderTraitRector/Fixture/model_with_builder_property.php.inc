<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\FooBuilder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected static string $builder = FooBuilder::class;
}

?>
-----
<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use App\Models\Builders\FooBuilder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\HasBuilder<\App\Models\Builders\FooBuilder<$this>>
     */
    use \Illuminate\Database\Eloquent\HasBuilder;
    protected $table = 'comments';

    protected static string $builder = FooBuilder::class;
}

?>
