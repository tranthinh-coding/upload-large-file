<?php

return [
    /**
     * The path to store the temporary file
     */
    'temp_path' => 'chunk-upload-temp',

    /**
     * The expiration time for the temporary file
     * Default: 1 day
     */
    'chunk_expire' => 60 * 60 * 24,

    /**
     * The path to store the final file
     */
    'dir_path' => 'uploads',
];
