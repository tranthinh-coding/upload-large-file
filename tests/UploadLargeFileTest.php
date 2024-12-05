<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Should validate request', function () {
    it('file required', function () {
        $request = new \Illuminate\Http\Request;
        $request->merge([
            'total_chunks_file' => 1,
            'filename' => 'test.txt',
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The file field is required.');
    });

    it('file should be a file', function () {
        $request = new \Illuminate\Http\Request;
        $request->merge([
            'total_chunks_file' => 1,
            'filename' => 'test.txt',
            'file' => 'test',
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The file field must be a file.');
    });

    it('total_chunks_file is integer', function () {
        Storage::fake('local');
        $request = new \Illuminate\Http\Request;

        // fake a txt file
        $request->merge([
            'total_chunks_file' => 'string',
            'filename' => 'test.txt',
            'file' => UploadedFile::fake()->create('test.txt', 1000),
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The total chunks file field must be an integer.');
    });

    it('total_chunks_file required', function () {
        Storage::fake('local');
        $request = new \Illuminate\Http\Request;

        // fake a txt file
        $request->merge([
            'filename' => 'test.txt',
            'file' => UploadedFile::fake()->create('test.txt', 1000),
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The total chunks file field is required.');
    });

    it('filename required', function () {
        Storage::fake('local');
        $request = new \Illuminate\Http\Request;

        // fake a txt file
        $request->merge([
            'total_chunks_file' => 1,
            'file' => UploadedFile::fake()->create('test.txt', 1000),
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The filename field is required.');
    });

    it('filename is string', function () {
        Storage::fake('local');
        $request = new \Illuminate\Http\Request;

        // fake a txt file
        $request->merge([
            'total_chunks_file' => 1,
            'filename' => 1,
            'file' => UploadedFile::fake()->create('test.txt', 1000),
        ]);

        $uploadLargeFile = new \Think\UploadLargeFile\UploadLargeFile;

        $res = $uploadLargeFile->upload($request);

        expect($res['message'])->toBe('The filename field must be a string.');
    });
});
