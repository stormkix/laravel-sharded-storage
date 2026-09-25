<?php

namespace Stormkix\ShardedStorage;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ShardedStorage
{
    private ?string $disk = null;

    public function __construct(
        private readonly FilesystemFactory $filesystem,
        private readonly PathSharder $sharder,
        private readonly array $config = [],
    ) {
    }

    /**
     * Andere Disk für den nächsten Aufruf verwenden:
     * ShardedStorage::disk('s3')->store($file, 'documents');
     */
    public function disk(?string $disk): static
    {
        $clone = clone $this;
        $clone->disk = $disk;

        return $clone;
    }

    /**
     * Speichert eine Datei mit generiertem Namen und gibt den relativen Pfad zurück.
     * Diesen Pfad in der Datenbank speichern!
     */
    public function store(File|UploadedFile $file, string $directory = '', array $options = []): string|false
    {
        $name = $this->generateName($file);

        if ($this->usesContentNaming()) {
            $path = $this->pathFor($directory, $name);

            if ($this->filesystem()->exists($path)) {
                return $path; // identischer Inhalt existiert bereits
            }
        }

        return $this->storeAs($file, $directory, $name, $options);
    }

    /**
     * Speichert eine Datei unter einem vorgegebenen Namen (im passenden Shard).
     */
    public function storeAs(File|UploadedFile $file, string $directory, string $name, array $options = []): string|false
    {
        $path = $this->pathFor($directory, $name);
        $targetDirectory = dirname($path);

        return $this->filesystem()->putFileAs(
            $targetDirectory === '.' ? '' : $targetDirectory,
            $file,
            $name,
            $options,
        );
    }

    /**
     * Speichert rohen Inhalt (z. B. generierte PDFs) und gibt den Pfad zurück.
     */
    public function put(string $contents, string $extension, string $directory = '', array $options = []): string|false
    {
        $base = $this->usesContentNaming()
            ? hash($this->hashAlgorithm(), $contents)
            : Str::random(40);

        $path = $this->pathFor($directory, $base.'.'.ltrim($extension, '.'));

        if ($this->usesContentNaming() && $this->filesystem()->exists($path)) {
            return $path;
        }

        return $this->filesystem()->put($path, $contents, $options) ? $path : false;
    }

    /**
     * Berechnet den Pfad, ohne etwas zu speichern.
     */
    public function pathFor(string $directory, string $name): string
    {
        return $this->sharder->path($directory, $name);
    }

    protected function generateName(File|UploadedFile $file): string
    {
        if (! $this->usesContentNaming()) {
            return $file->hashName();
        }

        $hash = hash_file($this->hashAlgorithm(), $file->getRealPath());
        $extension = $file->guessExtension() ?: $file->getExtension();

        return $extension ? $hash.'.'.$extension : $hash;
    }

    protected function filesystem(): Filesystem
    {
        return $this->filesystem->disk($this->disk ?? ($this->config['disk'] ?? null));
    }

    protected function usesContentNaming(): bool
    {
        return ($this->config['naming'] ?? 'random') === 'content';
    }

    protected function hashAlgorithm(): string
    {
        return $this->config['hash_algorithm'] ?? 'sha256';
    }
}
