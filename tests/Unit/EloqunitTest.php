<?php
namespace Eloqunit\Test\Unit;

use Eloqunit\Constraint;
use Eloqunit\Eloqunit;
use Illuminate\Database\Capsule\Manager;

class EloqunitTest extends Eloqunit
{
    private static $db = null;

    public function setup(): void
    {
        if (null === static::$db) {
            static::$db = new Manager();
            static::$db->addConnection([
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
            static::$db->setAsGlobal();
            static::$db->bootEloquent();
        }
        static::$db->getConnection()->statement('create table foo (id string, value string, created_at timestamp default CURRENT_DATE, updated_at timestamp)');
    }

    public function tearDown(): void
    {
        static::$db->getConnection()->statement('drop table foo');
    }

    public function getDatabase(): Manager
    {
        return static::$db;
    }

    public function testNoRowsExistInitially()
    {
        $this->assertRowCount(0, 'foo');
    }

    public function testSeedAndAssert()
    {
        $this->assertRowCount(0, 'foo');
        $this->seed('foo', [['id' => 'id.one', 'value' => 'value.one'], ['id' => 'id.two', 'value' => 'value.two']]);
        $this->assertRowCount(2, 'foo');
        $this->assertRowExists('foo', ['id' => 'id.one']);
        $this->assertRowExists('foo', ['id' => 'id.two']);
        $this->assertRowNotExists('foo', ['id' => 'id.three']);
    }

    public function testAssertRowMatches()
    {
        $this->seed('foo', [
            [
                'id' => 'foo',
                'value' => 'bar',
            ]
        ]);
        $this->assertRowMatches('foo', ['id' => 'foo'], [
            'created_at' => Constraint::IsNotNull(),
            'updated_at' => Constraint::IsNull(),
        ]);
        $this->assertRowMatches('foo', ['id' => 'foo'], ['value' => 'bar']);
    }

    public function testAssertRowCountWithConstraints()
    {
        $this->seed('foo', [
            ['id' => '1', 'value' => null],
            ['id' => '2', 'value' => null],
            ['id' => '3', 'value' => 'null'],
        ]);
        $this->assertRowCount(2, 'foo', ['value' => Constraint::IsNull()], 'two null rows');
        $this->assertRowCount(1, 'foo', ['value' => Constraint::IsNotNull()], 'one not-null row');
    }
}
