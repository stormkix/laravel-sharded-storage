<?php

namespace Stormkix\ShardedStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\ServiceProvider;

class ShardedStorageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sharded-storage.php', 'sharded-storage');

        $this->app->singleton(PathSharder::class, fn ($app) => new PathSharder(
            (int) $app['config']->get('sharded-storage.depth', 2),
            (int) $app['config']->get('sharded-storage.segment_length', 2),
        ));

        $this->app->singleton(ShardedStorage::class, fn ($app) => new ShardedStorage(
            $app['filesystem'],
            $app->make(PathSharder::class),
            $app['config']->get('sharded-storage', []),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sharded-storage.php' => config_path('sharded-storage.php'),
            ], 'sharded-storage-config');
        }

        // $request->file('upload')->storeSharded('documents');
        UploadedFile::macro('storeSharded', function (string $directory = '', ?string $disk = null) {
            /** @var UploadedFile $this */
            return app(ShardedStorage::class)->disk($disk)->store($this, $directory);
        });
    }
}
