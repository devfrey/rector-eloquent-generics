<?php

use Devfrey\RectorLaravel\Eloquent\DocumentRelationGenericsRector;
use Rector\Config\RectorConfig;

return function (RectorConfig $rectorConfig) {
    $rectorConfig->rule(DocumentRelationGenericsRector::class);
};
