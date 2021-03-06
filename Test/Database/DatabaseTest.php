<?php
namespace Test\Database;

use PHPUnit\Framework\TestCase;
use Test\Database\Column\InvalidMockColumnDefinition;
use Test\Database\Column\ValidMockColumnDefinition;
use Vadorco\Database\DatabaseActionException;
use Vadorco\Database\MySQLiDatabase;
use Vadorco\Database\PDODatabase;
use Vadorco\Database\Table\DatabaseTable;
use Vadorco\Database\Table\TableNotFoundException;

class DatabaseTest extends TestCase
{
    private $database;
    private $table_name = "mock-table";

    public function setUp(): void
    {
        //$this->database = new MySQLiDatabase("localhost", "orm", "M0xe0MeHwWzl9RMy", "php-orm");
        $this->database = new PDODatabase("mysql", "mysql:dbname=php-orm", "orm", "M0xe0MeHwWzl9RMy");
        //$this->database = new PDODatabase("sqlite", "sqlite:Resources/test.db", "", "");
    }

    public function tearDown(): void
    {
        if ($this->database->table_exists($this->table_name))
        {
            $this->database->remove_table($this->table_name);
        }

        $this->database->close();
    }

    public function test_create_table()
    {
        $this->create_table();
        $this->assert_table_exists();
    }

    private function create_table(array $column_definitions = null)
    {
        if ($column_definitions == null)
        {
            $column_definitions = [
                new ValidMockColumnDefinition()
            ];
        }

        $this->database->create_table($this->table_name, $column_definitions);
    }

    private function assert_table_exists()
    {
        $this->assertTrue($this->database->table_exists($this->table_name));
    }

    public function test_remove_table()
    {
        $this->create_table();
        $this->remove_table();
        $this->assert_table_not_exists();
    }

    private function remove_table()
    {
        $this->database->remove_table($this->table_name);
    }

    private function assert_table_not_exists()
    {
        $this->assertFalse($this->database->table_exists($this->table_name));
    }

    public function test_create_table_while_already_exists()
    {
        $this->expectException(DatabaseActionException::class);
        $this->create_table();
        $this->create_table();
    }

    public function test_remove_not_existing_table()
    {
        $this->expectException(DatabaseActionException::class);
        $this->remove_table();
    }

    public function test_create_table_with_invalid_column_definition()
    {
        $column_definitions = [
            new InvalidMockColumnDefinition()
        ];

        $this->expectException(DatabaseActionException::class);
        $this->create_table($column_definitions);
    }

    public function test_choose_existing_table()
    {
        $this->create_table();
        $table = $this->choose_table();
        $this->assertNotNull($table);
    }

    private function choose_table(): DatabaseTable
    {
        return $this->database->choose_table($this->table_name, ValidMockColumnDefinition::$primary_key_name);
    }

    public function test_choose_not_existing_table()
    {
        $this->expectException(TableNotFoundException::class);
        $this->choose_table();
    }
}
