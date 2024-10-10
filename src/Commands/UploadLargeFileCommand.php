<?php

namespace Think\UploadLargeFile\Commands;

use Illuminate\Console\Command;

class UploadLargeFileCommand extends Command
{
    public $signature = 'upload-large-file:prune-chunks-expired';

    public $description = 'Prune expired upload chunks';

    public function handle(): int
    {
        $tempPath = config('upload-large-file.temp_path');
        $expire = config('upload-large-file.chunk_expire');

        if (! file_exists($tempPath)) {
            return self::FAILURE;
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

        return self::SUCCESS;
    }
}
