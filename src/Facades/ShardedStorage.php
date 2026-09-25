<?php

namespace Stormkix\ShardedStorage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Stormkix\ShardedStorage\ShardedStorage disk(?string $disk)
 * @method static string|false store(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $file, string $directory = '', array $options = [])
 * @method static string|false storeAs(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $file, string $directory, string $name, array $options = [])
 * @method static string|false put(string $contents, string $extension, string $directory = '', array $options = [])
 * @method static string pathFor(string $directory, string $name)
 *
 * @see \Stormkix\ShardedStorage\ShardedStorage
 */
class ShardedStorage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Stormkix\ShardedStorage\ShardedStorage::class;
    }
}
