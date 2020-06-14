<?php
namespace Source\PDO\Table;

use PDO;
use PDOStatement;
use Source\Database\DatabaseActionException;
use Source\Database\Table\DatabaseTable;
use Source\Database\Table\InvalidPrimaryKeyException;

class PDODatabaseTable implements DatabaseTable
{
    private $name;
    private $primary_key_name;
    private $pdo;

    public function __construct(string $name, string $primary_key_name, PDO $pdo)
    {
        $this->name = $name;
        $this->primary_key_name = $primary_key_name;
        $this->pdo = $pdo;
    }

    /**
     * @param mixed $entry An associative array representing a record stored in the table.
     *                     Each element of the array is pointing from the column name to value: "column-name" => "value".
     * @return int Index of inserted record. If the table has an autoincrement index, value of the index will be returned.
     * @throws DatabaseActionException Thrown when unable to execute the query.
     */
    public function insert(array $entry): int
    {
        $columns = array_keys($entry);
        $columns_placeholder = implode("`, `", $columns);

        $values = array_values($entry);
        $values_placeholder = str_repeat("?, ", sizeof($values) - 1) . "?";

        $query = "INSERT INTO `{$this->name}` (`$columns_placeholder`) VALUES ($values_placeholder);";
        $statement = $this->pdo->prepare($query);
        $statement->execute($values);

        if ($statement->rowCount() == 0)
        {
            throw new DatabaseActionException();
        }

        return $this->pdo->lastInsertId();
    }

    /**
     * @param mixed $primary_key_value A primary key pointing to the record that will be updated.
     * @param mixed $entry An associative array representing a record stored in the table.
     *                     Each element of the array is pointing from the column name to value: "column-name" => "value".
     * @throws DatabaseActionException Thrown when unable to execute query updating the table.
     * @throws InvalidPrimaryKeyException Thrown when none of the record has been updated due to invalid primary key.
     */
    public function update($primary_key_value, array $entry): void
    {
        $mapping_placeholder = $this->get_mapping_placeholder($entry);
        $query = "UPDATE `{$this->name}` SET $mapping_placeholder WHERE `{$this->primary_key_name}` = ?;";
        $statement = $this->pdo->prepare($query);
        $parameters = array_values($entry);

        array_push($parameters, $primary_key_value);

        if ($statement->execute($parameters))
        {
            if ($statement->rowCount() == 0)
            {
                throw new InvalidPrimaryKeyException();
            }
        }
        else
        {
            throw new DatabaseActionException();
        }
    }

    /**
     * @param mixed $entry An associative array representing a record stored in the table.
     *                     Each element of the array is pointing from the column name to value: "column-name" => "value".
     * @return string A placeholder used by query-preparing mechanism.
     *                Each column name of the entry will be assigned to the question mark and imploded with a comma, for example:
     *                "column_name_1 = ?, column_name_2 = ?, column_name_3 = ?".
     *                This placeholder is used to build prepared statement taking into account three columns.
     */
    private function get_mapping_placeholder($entry)
    {
        $mapping_placeholders = array();

        foreach ($entry as $column => $value)
        {
            $mapping_placeholders[] = "`$column` = ?";
        }

        return implode(", ", $mapping_placeholders);
    }

    /**
     * @param mixed $primary_key_value A primary key pointing to the record that will be removed.
     * @throws InvalidPrimaryKeyException Thrown when $primary_key_value doesn't match any record in the table.
     */
    public function remove($primary_key_value): void
    {
        $query = "DELETE FROM `{$this->name}` WHERE `{$this->primary_key_name}` = :primary_key_value;";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(":primary_key_value", $primary_key_value);
        $statement->execute();

        if ($statement->rowCount() == 0)
        {
            throw new InvalidPrimaryKeyException();
        }
    }

    /**
     * @param mixed $primary_key_value A primary key pointing to the record that will be selected.
     * @return array An associative array representing a record stored in the table.
     *               Each element of the array is pointing from the column name to value: "column-name" => "value".
     * @throws InvalidPrimaryKeyException Thrown when $primary_key_value doesn't match any record in the table.
     */
    public function select($primary_key_value): array
    {
        $statement = $this->select_query($primary_key_value);
        $statement->execute();

        if ($result = $statement->fetch(PDO::FETCH_ASSOC))
        {
            return $result;
        }

        throw new InvalidPrimaryKeyException();
    }

    /**
     * @param mixed $primary_key_value A primary key identifying record which data will be selected.
     * @return PDOStatement An object representing prepared query responsible for selecting data from the database.
     */
    private function select_query($primary_key_value): PDOStatement
    {
        $query = "SELECT * FROM `{$this->name}` WHERE `{$this->primary_key_name}` = :primary_key_value;";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(":primary_key_value", $primary_key_value);

        return $statement;
    }

    /**
     * @return array An array of associative arrays representing records stored in the table.
     *               Each element of the associative array is pointing from the column name to value:
     *               "column-name" => "value".
     */
    public function select_all(): array
    {
        $query = "SELECT * FROM `{$this->name}`;";
        $statement = $this->pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * @param string $condition A condition that will be appended after WHERE clause to the query.
     * @return array An array of associative arrays representing records stored in the table.
     *               Each element of the associative array is pointing from the column name to value:
     *               "column-name" => "value".
     */
    public function select_where(string $condition): array
    {
        $query = "SELECT * FROM `{$this->name}` WHERE $condition;";
        $statement = $this->pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
}