<?php

use Devfrey\RectorLaravel\Eloquent\AddBuilderPropertyRector;
use Rector\Config\RectorConfig;

return function (RectorConfig $rectorConfig) {
    $rectorConfig->rule(AddBuilderPropertyRector::class);
};
