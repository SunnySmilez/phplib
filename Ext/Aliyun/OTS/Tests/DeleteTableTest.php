<?php
namespace OTS\Tests;

use OTS;

require __DIR__ . "/../../../vendor/autoload.php";

SDKTestBase::cleanUp();

class DeleteTableTest extends SDKTestBase {

    /*     *
     * 
     * DeleteTable
     * 创建一个表，并删除，ListTable期望返回0个TableName。
     */

    public function testDeleteTable() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "myTable",
                "primary_key_schema" => array(
                    "PK1" => "STRING",
                    "PK2" => "INTEGER",
                    "PK3" => "STRING",
                    "PK4" => "INTEGER"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        $this->otsClient->createTable($tablebody);

        $request = array(
                    "table_name" => "myTable"
                );
        //print_r($this->listtable->ListTable());
        $response = $this->otsClient->deleteTable($request);
        $this->assertEquals($response, array());
        $this->assertEmpty($this->otsClient->listTable(array()));
    }

    /*     *
     * 
     * DeleteTableEmpty
     * 指定表名为空，抛出对应错误信息 Invalid table name: ''.
     */

    public function testDeleteTableEmpty() {
        $request = array(
                    "table_name" => ""
                );

        try {
            $this->otsClient->deleteTable($request);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
       
    }

    /*     *
     * 
     * DeleteTableEmpty
     * 指定不存在的表，抛出对应错误信息  Requested table does not exist
     */

    public function testNotExiteTableName() {
        $request = array(
                    "table_name" => "TableThatNotExisting"
                );

        try {
            $this->otsClient->deleteTable($request);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Requested table does not exist.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }
}

