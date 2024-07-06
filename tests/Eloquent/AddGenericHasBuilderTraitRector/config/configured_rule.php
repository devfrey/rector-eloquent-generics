<?php

use Devfrey\RectorLaravel\Eloquent\AddGenericHasBuilderTraitRector;
use Rector\Config\RectorConfig;

return function (RectorConfig $rectorConfig) {
    $rectorConfig->rule(AddGenericHasBuilderTraitRector::class);
};
