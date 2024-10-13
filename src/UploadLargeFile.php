<?php

namespace Think\UploadLargeFile;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadLargeFile
{
    /**
     * The folder to store the final file
     * @var string
     */
    private string $folder;

    /**
     * The folder to store the temporary file
     * @var string
     */
    private string $tempPath;

    /**
     * The storage disk
     * @var Filesystem
     */
    private Filesystem $storage;

    /**
     * The cache driver
     * @var mixed
     */
    private CacheRepository $cache;

    public function __construct()
    {
        $this->folder = config('upload-large-file.dir_path');
        $this->tempPath = config('upload-large-file.temp_path');
        $this->storage = Storage::disk(config('upload-large-file.storage_disk'));
        $this->cache = Cache::driver(config('upload-large-file.cache_driver'));
    }

    /**
     * Get the upload id
     * Generate a unique id for the upload from request info: browser info, ip, uuid
     * @return string
     */
    private function getUploadId(): string
    {
        $ip = Request::ip();
        $userAgent = Request::userAgent();
        $uploadUUID = Request::get('uuid');

        return md5($ip . $userAgent . $uploadUUID);
    }

    /**
     * Set the folder to store the final file
     * @param string $folder
     * @return $this
     */
    public function folder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Validate the request
     * @return true|array
     */
    private function validate(): true|array
    {
        try {
            Request::validate([
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

    /**
     * Receive the request info
     * @return array
     */
    private function receiveRequestInfo(): array
    {
        $uploadId = $this->getUploadId();
        $totalFile = (int)Request::get('total_chunks_file');
        $filename = Request::get('filename');
        $chunk = Request::file('file');
        $randomChunkName = Str::random(40) . '.part';

        return [
            'uploadId' => $uploadId,
            'totalFile' => $totalFile,
            'filename' => $filename,
            'chunk' => $chunk,
            'randomChunkName' => $randomChunkName,
        ];
    }

    /**
     * Handle the received chunk
     * @param string $chunkName
     * @param int $totalFile
     * @param string $filename
     * @param string $uploadId
     * @return array
     */
    private function onReceivedChunk(string $chunkName, int $totalFile, string $filename, string $uploadId): array
    {
        if ($this->cache->has("upload_{$uploadId}_chunks") === false) {
            $this->cache->put("upload_{$uploadId}_chunks", 0);
        }

        $this->cache->increment("upload_{$uploadId}_chunks", 1);
        $receivedChunks = $this->cache->get("upload_{$uploadId}_chunks");

        $this->cache->put("upload_{$uploadId}_chunk_{$receivedChunks}", $chunkName);

        if ($receivedChunks === $totalFile) {
            $this->mergeChunkFiles($uploadId, $totalFile, $filename);
            $this->cache->forget("upload_{$uploadId}_chunks");

            return [
                'progress' => 1,
                'message' => 'File uploaded',
                'path' => $this->folder . '/' . $uploadId . '__' . $filename,
            ];
        }

        return [
            'message' => 'Chunk uploaded',
            'progress' => $receivedChunks / $totalFile,
        ];
    }

    /**
     * Handle upload the file
     * @return array
     */
    public function upload(): array
    {
        $validate = $this->validate();
        if ($validate !== true) {
            return $validate;
        }

        $this->ensureDirectoryExists($this->tempPath);
        $this->ensureDirectoryExists($this->folder);

        [
            'uploadId' => $uploadId,
            'totalFile' => $totalFile,
            'filename' => $filename,
            'chunk' => $chunk,
            'randomChunkName' => $randomChunkName
        ] = $this->receiveRequestInfo();

        $this->storage->putFileAs($this->tempPath, $chunk, $randomChunkName);

        return $this->onReceivedChunk($randomChunkName, $totalFile, $filename, $uploadId);
    }

    /**
     * Ensure the directory exists
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists($path): void
    {
        if (!$this->storage->exists($path)) {
            $this->storage->makeDirectory($path);
        }
    }

    /**
     * Merge the chunk files
     * @param string $uploadId
     * @param int $total
     * @param string $filename
     * @return void
     */
    private function mergeChunkFiles(string $uploadId, int $total, string $filename): void
    {
        $finalPath = $this->folder . '/' . $uploadId . '__' . $filename;
        $finalFile = fopen($this->storage->path($finalPath), 'wb');

        $chunkNames = [];
        for ($i = 1; $i <= $total; $i++) {
            $chunkNames[] = $this->cache->get("upload_" . $uploadId . "_chunk_" . $i);
        }

        foreach ($chunkNames as $chunkName) {
            $chunkPath = $this->tempPath . '/' . $chunkName;
            $chunk = fopen($this->storage->path($chunkPath), 'rb');
            while (!feof($chunk)) {
                fwrite($finalFile, fread($chunk, 1024));
            }
            fclose($chunk);

            $this->storage->delete($chunkPath);
        }

        fclose($finalFile);
    }
}
