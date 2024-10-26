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
     */
    private string $folder;

    /**
     * The folder to store the temporary file
     */
    private string $tempPath;

    /**
     * The storage disk
     */
    private Filesystem $storage;

    /**
     * The cache driver
     *
     * @var mixed
     */
    private CacheRepository $cache;

    public function __construct()
    {
        $this->folder = Utils::getDirPath();
        $this->tempPath = Utils::getTempPath();
        $this->storage = Storage::disk(Utils::getStorageDisk());
        $this->cache = Cache::driver(Utils::getCacheDriver());
    }

    /**
     * Set the folder to store the final file
     *
     * @return $this
     */
    public function folder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Validate the request
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
     */
    private function receiveRequestInfo(): array
    {
        $uploadId = Utils::getUploadId();
        $totalFile = (int) Request::get('total_chunks_file');
        $filename = Request::get('filename');
        $chunk = Request::file('file');
        $chunkNumber = Request::get('chunk_number');
        $randomChunkName = Str::random(40).'.part';

        return [
            'uploadId' => $uploadId,
            'totalFile' => $totalFile,
            'filename' => $filename,
            'chunk' => $chunk,
            'randomChunkName' => $randomChunkName,
            'chunkNumber' => $chunkNumber,
        ];
    }

    /**
     * Handle the received chunk
     */
    private function onReceivedChunk(string $chunkName, int $totalFile, string $filename, string $uploadId, int $chunkNumber): array
    {
        if ($this->cache->has("upload_{$uploadId}_chunks") === false) {
            $this->cache->put("upload_{$uploadId}_chunks", 0);
        }

        $this->cache->increment("upload_{$uploadId}_chunks", 1);
        $receivedChunks = $this->cache->get("upload_{$uploadId}_chunks");

        $this->cache->put("upload_{$uploadId}_chunk_{$chunkNumber}", $chunkName);

        if ($receivedChunks === $totalFile) {
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

    /**
     * Handle upload the file
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
            'randomChunkName' => $randomChunkName,
            'chunkNumber' => $chunkNumber,
        ] = $this->receiveRequestInfo();

        $this->storage->putFileAs($this->tempPath, $chunk, $randomChunkName);

        return $this->onReceivedChunk($randomChunkName, $totalFile, $filename, $uploadId, $chunkNumber);
    }

    /**
     * Ensure the directory exists
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (! $this->storage->exists($path)) {
            $this->storage->makeDirectory($path);
        }
    }

    /**
     * Merge the chunk files
     */
    private function mergeChunkFiles(string $uploadId, int $total, string $filename): void
    {
        $finalPath = $this->folder.'/'.$uploadId.'__'.$filename;
        $finalFile = fopen($this->storage->path($finalPath), 'wb');

        $chunkNames = [];
        for ($i = 1; $i <= $total; $i++) {
            $chunkNames[] = $this->cache->get('upload_'.$uploadId.'_chunk_'.$i);
        }

        foreach ($chunkNames as $chunkName) {
            $chunkPath = $this->tempPath.'/'.$chunkName;
            $chunk = fopen($this->storage->path($chunkPath), 'rb');
            while (! feof($chunk)) {
                fwrite($finalFile, fread($chunk, 1024));
            }
            fclose($chunk);

            $this->storage->delete($chunkPath);
        }

        fclose($finalFile);
    }
}
