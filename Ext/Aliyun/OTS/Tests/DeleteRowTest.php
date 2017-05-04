<?php

namespace OTS\Tests;

use OTS;

require __DIR__ . "/../../../vendor/autoload.php";

SDKTestBase::cleanUp();
SDKTestBase::createInitialTable(
    array(
        "table_meta" => array(
            "table_name" => "myTable",
            "primary_key_schema" => array(
                "PK1" => "INTEGER",
                "PK2" => "STRING",
            )
        ),
        "reserved_throughput" => array(
            "capacity_unit" => array(
                "read" => 100,
                "write" => 100,
            )
        ),
    )
);
SDKTestBase::waitForTableReady();

class DeleteRowTest extends SDKTestBase {

    /*     *
     * 
     * TableNameOfZeroLength
     * 创建一个表，并删除，ListTable期望返回0个TableName。
     */

    public function testTableNameOfZeroLength() {
        $deleterow = array(
            "table_name" => "",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1")
        );
        try {
            $this->otsClient->deleteRow($deleterow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 
     * 5ColumnInPK
     * 和表主键不一致，指定5个主键
     */

    public function testColumnInPK() {
        $deleterow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "aaa", "PK2" => "cc", "PK3" => "ccd", "PK4" => "cds", "PK5" => "11s"),
        );
        try {
            $this->otsClient->deleteRow($deleterow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4].";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 
     * ExpectExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_EXIST，期望服务端返回 Invalid Condition。
     */

    public function testExpectExistConditionWhenRowNotExist() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("att1" => "asds", "att2" => "sdsd")
        );
        $this->otsClient->putRow($tablename);
        $deleterow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_EXIST",
            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
        );
        try {
            $this->otsClient->deleteRow($deleterow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Condition check failed.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 
     * ExpectExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_EXIST，期望操作成功。
     */

    public function testExpectExistConditionWhenRowExist() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("att1" => "asds", "att2" => "sdsd")
        );
        $this->otsClient->putRow($tablename);
        $deleterow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_EXIST",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
        );
        $this->otsClient->deleteRow($deleterow);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        //print_r($getrow);die;
        $this->assertEmpty($getrow['row']['primary_key_columns']);
        $this->assertEmpty($getrow['row']['attribute_columns']);
    }
    
    /* *
     * 
     * ExpectNotExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_NOT_EXIST
     * 
     */


    public function testExpectNotExistConditionWhenRowNotExist() {
        $deleterow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_NOT_EXIST",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
        );
       try {
            $this->otsClient->deleteRow($deleterow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid condition: EXPECT_NOT_EXIST while deleting row.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
    }
    /*
     * ExpectNotExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_NOT_EXIST 
     */
    public function testExpectNotExistConditionWhenRowExist() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("att1" => "asds", "att2" => "sdsd")
        );
        $this->otsClient->putRow($tablename);
        $deleterow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_NOT_EXIST",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
        );
       try {
            $this->otsClient->deleteRow($deleterow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid condition: EXPECT_NOT_EXIST while deleting row.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
    }
}

