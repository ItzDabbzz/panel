<?php

namespace Tests\Unit\Repositories\Eloquent;

use App\Repositories\Eloquent\DatabaseRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Tests\TestCase;

class DatabaseRepositoryTest extends TestCase
{
    /**
     * Test that a strict database creation does not silently reuse an existing database,
     * while the default keeps the IF NOT EXISTS behavior.
     */
    public function test_create_database_only_adds_if_not_exists_when_requested()
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->expects('statement')->with('CREATE DATABASE IF NOT EXISTS `s1_example`')->andReturn(true);
        $connection->expects('statement')->with('CREATE DATABASE `s1_example`')->andReturn(true);

        $manager = Mockery::mock(DatabaseManager::class);
        $manager->shouldReceive('connection')->with('dynamic')->andReturn($connection);

        $repository = new DatabaseRepository($this->app, $manager);

        $this->assertTrue($repository->createDatabase('s1_example'));
        $this->assertTrue($repository->createDatabase('s1_example', false));
    }
}
