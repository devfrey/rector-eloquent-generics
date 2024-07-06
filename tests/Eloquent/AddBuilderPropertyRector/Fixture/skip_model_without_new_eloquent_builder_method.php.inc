<?php

namespace Tests\Eloquent\AddBuilderPropertyRector\Fixture;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function foo(): BelongsTo
    {
        return $this->belongsTo(Foo::class);
    }
}

?>
-----
<?php

namespace Tests\Eloquent\AddBuilderPropertyRector\Fixture;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function foo(): BelongsTo
    {
        return $this->belongsTo(Foo::class);
    }
}

?>
