<?php
namespace Source\Database\Table;

class MySQLiColumnDescriptor implements ColumnDescriptor
{
    public function describe(ColumnDefinition $column_definition): string
    {
        $description = "`" . $column_definition->get_name() . "` " . $column_definition->get_type();

        if ($column_definition->has_length())
        {
            $description .= "(" . $column_definition->get_length() . ")";
        }

        if ($column_definition->is_unique())
        {
            $description .= " UNIQUE";
        }

        if ($column_definition->is_not_null())
        {
            $description .= " NOT NULL";
        }

        if ($column_definition->has_default_value())
        {
            $description .= " DEFAULT \"" . $column_definition->get_default_value() . "\"";
        }

        if ($column_definition->is_primary_key())
        {
            $description .= " PRIMARY KEY";
        }

        if ($column_definition->is_autoincrement())
        {
            $description .= " AUTO_INCREMENT";
        }

        return $description;
    }
}