<?php

namespace Think\UploadLargeFile;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadLargeFile
{
    private string $folder;

    private string $tempPath;

    private Filesystem $storage;

    private $cache;

    public function __construct()
    {
        $this->folder = config('upload-large-file.dir_path');
        $this->tempPath = config('upload-large-file.temp_path');
        $this->storage = Storage::disk('local');
        $this->cache = Cache::driver('file');
    }

    private function createUploadId(): string
    {
        return Str::uuid()->toString();
    }

    public function folder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    private function validate(Request $request): true|array
    {
        try {
            $request->validate([
                'total_chunks_file' => 'required|integer',
                'filename' => 'required|string',
                'file' => 'required|file',
            ]);

            return true;
        } catch (\Throwable $th) {
            return [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
                'th' => $th,
            ];
        }
    }

    private function receiveRequestInfo(Request $request): array
    {
        $uploadId = $this->createUploadId();
        $totalFile = (int) $request->get('total_chunks_file');
        $filename = $request->get('filename');
        $chunk = $request->file('file');
        $randomChunkName = Str::random(40).'.part';

        return [
            'uploadId' => $uploadId,
            'totalFile' => $totalFile,
            'filename' => $filename,
            'chunk' => $chunk,
            'randomChunkName' => $randomChunkName,
        ];
    }

    private function receiveChunk($chunkName, $totalFile, $filename, $uploadId): array
    {
        $receivedChunks = $this->cache->increment("upload_{$uploadId}_chunks", 1);
        $this->cache->put("upload_{$uploadId}_chunk_{$receivedChunks}", $chunkName);

        if ($receivedChunks == $totalFile) {
            $this->mergeChunkFiles($uploadId, $totalFile, $filename);
            $this->cache->forget("upload_{$uploadId}_chunks");

            return [
                'progress' => 1,
                'message' => 'File uploaded',
                'path' => $this->folder.'/'.$uploadId.'__'.$filename,
            ];
        }

        return [
            'message' => 'Chunk uploaded',
            'progress' => $receivedChunks / $totalFile,
        ];
    }

    public function upload(Request $request): array
    {
        $validate = $this->validate($request);
        if ($validate !== true) {
            return $validate;
        }

        $this->ensureDirectoryExists($this->tempPath);
        $this->storage->makeDirectory($this->tempPath);

        [
            'uploadId' => $uploadId,
            'totalFile' => $totalFile,
            'filename' => $filename,
            'chunk' => $chunk,
            'randomChunkName' => $randomChunkName
        ] = $this->receiveRequestInfo($request);

        $this->ensureDirectoryExists($this->tempPath);

        $this->storage->putFileAs($this->tempPath, $chunk, $randomChunkName);

        return $this->receiveChunk($randomChunkName, $totalFile, $filename, $uploadId);
    }

    private function ensureDirectoryExists($path): void
    {
        if (! $this->storage->exists($path)) {
            $this->storage->makeDirectory($path);
        }
    }

    private function mergeChunkFiles(string $uploadId, int $total, string $filename): void
    {
        $tempPath = 'temp/'.$uploadId;
        $finalPath = 'uploads/'.$uploadId.'__'.$filename;

        if (! $this->storage->exists('uploads')) {
            $this->storage->makeDirectory('uploads');
        }

        $finalFile = fopen(storage_path('app/'.$finalPath), 'wb');

        $chunkFiles = [];
        for ($i = 1; $i <= $total; $i++) {
            $chunkFiles[] = $this->cache->get("upload_{$uploadId}_chunk_{$i}");
        }
        foreach ($chunkFiles as $chunkFile) {
            $chunkPath = $tempPath.'/'.$chunkFile;
            $chunk = fopen(storage_path('app/'.$chunkPath), 'rb');
            while (! feof($chunk)) {
                fwrite($finalFile, fread($chunk, 1024));
            }
            fclose($chunk);
        }

        fclose($finalFile);

        $this->storage->deleteDirectory($tempPath);
    }
}
