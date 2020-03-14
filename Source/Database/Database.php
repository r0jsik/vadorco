<?php
interface Database
{
    public function table_exists($name) : bool;
    public function create_table($name, $columns);
    public function choose_table($name) : DatabaseTable;
}