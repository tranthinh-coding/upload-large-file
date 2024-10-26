<?php

namespace Think\UploadLargeFile;

class Utils
{
    /*
    |
    |--------------------------------------------------------------------------
    | Get the upload id
    |--------------------------------------------------------------------------
    |
    | Generate a unique id for the upload from request info: browser info, ip, uuid
    |
    */
    public static function getUploadId(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $uploadUUID = $_GET['uuid'] ?? $_POST['uuid'] ?? '';

        return md5($ip.$userAgent.$uploadUUID);
    }

    /*
    |
    |--------------------------------------------------------------------------
    | Get the temporary file path
    |--------------------------------------------------------------------------
    |
    */
    public static function getTempPath(): string
    {
        return config('upload-large-file.temp_path', 'chunk-upload-temp');
    }

    /*
     |--------------------------------------------------------------------------
     | Get the directory path to store the final file
     |--------------------------------------------------------------------------
     |
     */
    public static function getDirPath(): string
    {
        return config('upload-large-file.dir_path', 'uploads');
    }

    /*
     |--------------------------------------------------------------------------
     | Get the storage disk
     |--------------------------------------------------------------------------
     |
     */
    public static function getStorageDisk(): string
    {
        return config('upload-large-file.storage_disk', 'local');
    }

    /*
     |--------------------------------------------------------------------------
     | Get the cache driver
     |--------------------------------------------------------------------------
     |
     */
    public static function getCacheDriver(): string
    {
        return config('upload-large-file.cache_driver', 'file');
    }

    /*
     |--------------------------------------------------------------------------
     | Get the temporary file expire time
     |--------------------------------------------------------------------------
     |
     */
    public static function getChunkExpire(): int
    {
        return config('upload-large-file.chunk_expire', 60 * 60 * 24);
    }

    /*
     |--------------------------------------------------------------------------
     | Get the resource subdir rule
     |--------------------------------------------------------------------------
     |
     */
    public static function getResourceSubdirRule(): string
    {
        return config('upload-large-file.resource_subdir_rule', 'month');
    }

    /*
     |--------------------------------------------------------------------------
     | Generate the sub directory name
     |--------------------------------------------------------------------------
     |
     */
    public static function generateSubDirName(): string
    {
        $rule = self::getResourceSubdirRule();

        return match ($rule) {
            'year' => @date('Y', time()),
            'month' => @date('Ym', time()),
            'date' => @date('Ymd', time()),
            default => null,
        };
    }

    /*
     |--------------------------------------------------------------------------
     | Prune expired upload chunks
     |--------------------------------------------------------------------------
     |
     */
    public static function pruneChunksExpired(): bool
    {
        $tempPath = self::getTempPath();
        $expire = self::getChunkExpire();

        if (! file_exists($tempPath)) {
            return false;
        }

        $files = scandir($tempPath);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $tempPath.'/'.$file;
            $fileTime = filemtime($filePath);
            $now = time();

            if ($now - $fileTime > $expire) {
                unlink($filePath);
            }
        }

        return true;
    }
}
