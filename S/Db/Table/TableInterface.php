<?php
namespace S\Db\Table;

interface TableInterface {

    public function __construct($name, array $config);

    public function putRow($table, array $primary_key, array $data);

    public function getRow($table, array $primary_key, array $cols);

    public function deleteRow($table, array $primary_key);

}