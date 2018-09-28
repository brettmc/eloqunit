<?php
namespace Eloqunit;

use PHPUnit\Framework\TestCase;

abstract class Eloqunit extends TestCase implements EloqunitInterface
{
    public function setup()
    {
        $db = $this->getDatabase();
        $db->getConnection()->beginTransaction();
        parent::setup();
    }

    public function tearDown()
    {
        $db = $this->getDatabase();
        $db->getConnection()->rollback();
        parent::tearDown();
    }

    /**
     * Seed $table with rows from $data
     * @param string $table
     * @param array $data [['foo' => 'bar'], ['foo' => 'baz'], ...]
     * @return void
     */
    public function seed(string $table, array $data): void
    {
        $this->getDatabase()->table($table)->insert($data);
    }

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
            $select->where($key, $value);
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
            $this->fail('No matching row was found');
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
