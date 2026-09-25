<?php

namespace Stormkix\ShardedStorage\Tests;

use Stormkix\ShardedStorage\PathSharder;
use PHPUnit\Framework\TestCase as BaseTestCase;

class PathSharderTest extends BaseTestCase
{
    public function test_it_uses_the_leading_characters_of_a_hash_name(): void
    {
        $sharder = new PathSharder(2, 2);

        $this->assertSame('a3/f9', $sharder->shard('a3f9c2e1.pdf'));
        $this->assertSame('documents/a3/f9/a3f9c2e1.pdf', $sharder->path('documents', 'a3f9c2e1.pdf'));
    }

    public function test_non_hash_names_get_a_stable_hashed_shard(): void
    {
        $sharder = new PathSharder(2, 2);

        $first = $sharder->shard('Mein Vertrag.pdf');

        $this->assertMatchesRegularExpression('#^[0-9a-f]{2}/[0-9a-f]{2}$#', $first);
        $this->assertSame($first, $sharder->shard('Mein Vertrag.pdf'));
    }

    public function test_depth_zero_disables_sharding(): void
    {
        $sharder = new PathSharder(0, 2);

        $this->assertSame('documents/file.pdf', $sharder->path('/documents/', 'file.pdf'));
    }

    public function test_empty_directory_is_handled(): void
    {
        $this->assertSame('ab/cd/abcdef.txt', (new PathSharder(2, 2))->path('', 'abcdef.txt'));
    }
}
