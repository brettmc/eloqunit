<?php
namespace Eloqunit;

use PHPUnit\Framework\TestCase;

abstract class Eloqunit extends TestCase implements EloqunitInterface
{
    public function setup(): void
    {
        $db = $this->getDatabase();
        $db->getConnection()->beginTransaction();
    }

    public function tearDown(): void
    {
        $db = $this->getDatabase();
        $db->getConnection()->rollback();
    }

    /**
     * Seed $table with rows from $data
     * @param string $table
     * @param array $data [['foo' => 'bar'], ['foo' => 'baz'], ...]
     * @return void
     */
    public function seed($table, $data): void
    {
        $this->getDatabase()->table($table)->insert($data);
    }

    /**
     * Seed multiple database tables. Array keys represent table names, and values are an array of rows.
     * @param array $data ['table_one' => [[<row>,<row>]], 'table_two' => [[<row>,<row>]]
     * @return void
     */
    public function seedTables(array $data): void
    {
        foreach ($data as $table => $data) {
            $this->seed($table, $data);
        }
    }

    /**
     * Assert the rowcount on a table, optionally filtering by $keys
     * @param int $expected
     * @param string $table
     * @param array $keys
     * @param string $message
     * @return void
     */
    public function assertRowCount(int $expected, string $table, array $keys = [], string $message = ''): void
    {
        $this->assertEquals($expected, $this->rowCount($table, $keys), $message);
    }

    /**
     * Assert that a row exists in $table matching $keys
     * @param string $table
     * @param array $keys
     * @param string $message
     * @return void
     */
    protected function assertRowExists(string $table, array $keys, string $message = null): void
    {
        $this->assertGreaterThan(0, $this->rowCount($table, $keys), $message ?? 'matching row found');
    }

    /**
     * Assert that a row does not exist in $table matching $keys
     * @param string $table
     * @param array $keys
     * @param string $message
     * @return void
     */
    protected function assertRowNotExists(string $table, array $keys, string $message = null): void
    {
        $this->assertEquals(0, $this->rowCount($table, $keys), $message ?? 'no matching row found');
    }

    private function rowCount(string $table, array $keys): int
    {
        $db = $this->getDatabase();
        $select = $db->table($table)->select();
        foreach ($keys as $key => $value) {
            switch (is_object($value) ? get_class($value) : 'string') {
                case Constraints\IsNotNull::class:
                    $select->whereNotNull($key);
                    break;
                case Constraints\IsNull::class:
                    $select->whereNull($key);
                    break;
                default:
                    $select->where($key, $value);
            }
        }
        return $select->count();
    }

    /**
     * Verify that a row is found in $table matching $keys, and then make individual assertions
     * on $fields that they match a string or an Eloqunit constraint.
     *
     * @param string $table
     * @param array $keys ['key1' => 'value1', 'key2' => 'value2']
     * @param array $fields ['fieldName' => string|IsNull|IsNotNull]
     * @return void
     */
    protected function assertRowMatches(string $table, array $keys, array $fields): void
    {
        $select = $this->getDatabase()->table($table)->select();
        foreach ($keys as $key => $value) {
            $select->where($key, $value);
        }
        $row = $select->first();
        if (!$row) {
            $this->fail('No matching row was found'); //@codeCoverageIgnore
        }
        foreach ($fields as $key => $value) {
            switch (is_object($value) ? get_class($value) : 'string') {
                case Constraints\IsNotNull::class:
                    $this->assertNotEquals(null, $row->$key, sprintf('%s.%s is not null', $table, $key));
                    break;
                case Constraints\IsNull::class:
                    $this->assertEquals(null, $row->$key, sprintf('%s.%s is null', $table, $key));
                    break;
                default:
                    $this->assertEquals($value, $row->$key);
            }
        }
    }
}
