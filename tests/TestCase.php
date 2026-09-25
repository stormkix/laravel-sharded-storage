<?php

namespace Stormkix\ShardedStorage\Tests;

use Stormkix\ShardedStorage\ShardedStorageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ShardedStorageServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['ShardedStorage' => \Stormkix\ShardedStorage\Facades\ShardedStorage::class];
    }
}
