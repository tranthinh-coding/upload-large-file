<?php

namespace Think\UploadLargeFile;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Think\UploadLargeFile\Commands\UploadLargeFileCommand;

class UploadLargeFileServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('upload-large-file')
            ->hasCommand(UploadLargeFileCommand::class)
            ->hasConfigFile();
    }
}
