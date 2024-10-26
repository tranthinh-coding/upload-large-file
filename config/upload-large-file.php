<?php

return [
    /*
     |--------------------------------------------------------------------------
     | The temporary file path
     |--------------------------------------------------------------------------
     */
    'temp_path' => 'chunk-upload-temp',

    /*
     | The temporary file expire time
     |
     | Default: 24h
     */
    'chunk_expire' => 60 * 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | The directory path to store the final file
    |--------------------------------------------------------------------------
    |
     */
    'dir_path' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | The subdirectory rule
    |--------------------------------------------------------------------------
    |
    | * General settings: Organize by year, month, date, subdirectory, or none.
    |
    | * Supported options: 'year', 'month', 'date', null
    |
    */
    'resource_subdir_rule' => null,

    'storage_disk' => 'local',

    'cache_driver' => 'file',

];
