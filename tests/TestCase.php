<?php

namespace Think\UploadLargeFile\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Think\UploadLargeFile\UploadLargeFileServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            UploadLargeFileServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {}
}
