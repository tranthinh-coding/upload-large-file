<?php

namespace Think\UploadLargeFile\Commands;

use Illuminate\Console\Command;
use Think\UploadLargeFile\Utils;

class UploadLargeFileCommand extends Command
{
    public $signature = 'upload-large-file:prune-chunks-expired';

    public $description = 'Prune expired upload chunks';

    public function handle(): int
    {
        $pruneSuccess = Utils::pruneChunksExpired();

        if ($pruneSuccess) {
            $this->info('Expired chunks pruned successfully.');
            return self::FAILURE;
        }

        $this->error('Failed to prune expired chunks.');
        return self::SUCCESS;
    }
}
