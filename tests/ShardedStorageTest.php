<?php

namespace Stormkix\ShardedStorage\Tests;

use Stormkix\ShardedStorage\Facades\ShardedStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ShardedStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_it_stores_an_upload_in_a_sharded_directory(): void
    {
        $file = UploadedFile::fake()->create('vertrag.pdf', 100);

        $path = ShardedStorage::store($file, 'documents');

        $this->assertMatchesRegularExpression('#^documents/[a-z0-9]{2}/[a-z0-9]{2}/[A-Za-z0-9]{40}\.pdf$#', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_the_upload_macro_works(): void
    {
        $path = UploadedFile::fake()->create('avatar.pdf', 10)->storeSharded('avatars', 'local');

        $this->assertStringStartsWith('avatars/', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_content_naming_deduplicates_identical_files(): void
    {
        config()->set('sharded-storage.naming', 'content');
        $this->app->forgetInstance(\Stormkix\ShardedStorage\ShardedStorage::class);
        ShardedStorage::clearResolvedInstances();

        $a = ShardedStorage::store(UploadedFile::fake()->createWithContent('a.txt', 'hallo'), 'docs');
        $b = ShardedStorage::store(UploadedFile::fake()->createWithContent('b.txt', 'hallo'), 'docs');

        $this->assertSame($a, $b);
        $this->assertSame('docs/'.substr(hash('sha256', 'hallo'), 0, 2), substr($a, 0, 7));
    }

    public function test_put_stores_raw_contents(): void
    {
        $path = ShardedStorage::put('%PDF-1.4 ...', 'pdf', 'reports');

        $this->assertMatchesRegularExpression('#^reports/[a-z0-9]{2}/[a-z0-9]{2}/[A-Za-z0-9]{40}\.pdf$#', $path);
        $this->assertSame('%PDF-1.4 ...', Storage::disk('local')->get($path));
    }
}
