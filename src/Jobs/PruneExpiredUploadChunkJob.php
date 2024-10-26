<?php

namespace Think\UploadLargeFile\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Think\UploadLargeFile\Utils;

class PruneExpiredUploadChunkJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Utils::pruneChunksExpired();
    }
}

