<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\HasBuilder<\App\Models\Builders\PostBuilder>
     */
    use HasBuilder;
    use SoftDeletes;
}

?>
-----
<?php

namespace Tests\Eloquent\AddGenericHasBuilderTraitRector\Fixture;

use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /**
     * @use \Illuminate\Database\Eloquent\HasBuilder<\App\Models\Builders\PostBuilder>
     */
    use HasBuilder;
    use SoftDeletes;
}

?>
