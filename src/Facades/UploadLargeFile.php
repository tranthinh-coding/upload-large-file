<?php

namespace Think\UploadLargeFile\Facades;

/**
 * @see \Think\UploadLargeFile\UploadLargeFile
 */
class UploadLargeFile extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Think\UploadLargeFile\UploadLargeFile::class;
    }

    public static function folder(string $folder): \Think\UploadLargeFile\UploadLargeFile
    {
        return static::getFacadeRoot()->folder($folder);
    }

    public static function upload(\Illuminate\Http\Request $request): array|string
    {
        return static::getFacadeRoot()->upload($request);
    }
}
