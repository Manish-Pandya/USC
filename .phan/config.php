<?php

use Phan\Config;

return [
    "allow_missing_properties" => true,
    "null_casts_as_any_type" => true,
    'backward_compatibility_checks' => true,
    "quick_mode" => true,
    "minimum_severity" => 5,
    'directory_list' => [
         'rsms/src',
         'rsms/test'
    ],

    "exclude_analysis_directory_list" => [
         'rsms/src/logs',
         'rsms/src/logging',
         'rsms/src/font',
         'rsms/src/css',
         'rsms/src/sass',
         'rsms/src/sql',
         'rsms/test/simpletest'
    ],
];
