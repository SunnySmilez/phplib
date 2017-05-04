<?php

namespace OTS\Tests;

use OTS;

require __DIR__ . "/../../../vendor/autoload.php";


class CreateTableTest extends SDKTestBase {

    public function setup() {
        $table_name = $this->otsClient->listTable(array());
        for ($i = 0; $i < count($table_name); $i++) {
            $tablename['table_name'] = $table_name[$i];
            $this->otsClient->deleteTable($tablename);
        }
    }

    /*     *
     * 
     * CreateTable
     * 创建一个表，然后DescribeTable校验TableMeta和ReservedThroughput与建表时的参数一致
     */

    public function testCreateTable() {
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
        $tablename = array("myTable");
        //$tablename['mytable'] = 111;
        $this->assertEquals($this->otsClient->listTable(array()), $tablename);
        //$this->assertContains();
        $table_name['table_name'] = "myTable";
        $teturn = array(
            "table_name" => "myTable",
            "primary_key_schema" => array(
                "PK1" => "STRING",
                "PK2" => "INTEGER",
                "PK3" => "STRING",
                "PK4" => "INTEGER"
            )
        );
        $table_meta = $this->otsClient->describeTable($table_name);
        $this->assertEquals($teturn, $table_meta['table_meta']);
        //$this->otsClient->deleteTable($table_name);
    }

    /*     *
     * TableNameOfZeroLength
     * 表名长度为0的情况，期望返回错误消息：Invalid table name: ''. 中包含的TableName与输入一致
     */

    public function testTableNameOfZeroLength() {

        $tablebody = array(
            "table_meta" => array(
                "table_name" => "",
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
        try {
            $this->otsClient->createTable($tablebody);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * TableNameWithUnicode
     * 表名包含Unicode，期望返回错误信息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致
     */

    public function testTableNameWithUnicode() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "testU+0053",
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
        try {
            $this->otsClient->createTable($tablebody);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: '{$tablebody['table_meta']['table_name']}'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 1KBTableName
     * 表名长度为1KB，期望返回错误信息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致
     */

    public function testTableName1KB() {
        $name = "";
        for ($i = 1; $i < 1025; $i++) {
            $name .="a";
        }
        $tablebody = array(
            "table_meta" => array(
                "table_name" => $name,
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
        try {
            $this->otsClient->createTable($tablebody);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: '{$tablebody['table_meta']['table_name']}'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * NoPKInSchema
     * 测试CreateTable在TableMeta包含0个PK时的情况，期望返回错误消息：Failed to parse the ProtoBuf message
     */

    public function testNoPKInSchema() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test2",
                "primary_key_schema" => array(),
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
         try {
            $this->otsClient->createTable($tablebody);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4]."; // TODO make right expect
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * OnePKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含1个PK时的情况
     */

    public function testOnePKInSchema() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test3",
                "primary_key_schema" => array(
                    "PK1" => "STRING"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        $this->assertEmpty($this->otsClient->createTable($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array(
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array(
                "PK1" => "STRING"
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);
    }

    /*     *
     * FourPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含4个PK时的情况
     */

    public function testFourPKInSchema() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test4",
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

        $this->assertEmpty($this->otsClient->createTable($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array(
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array(
                "PK1" => "STRING",
                "PK2" => "INTEGER",
                "PK3" => "STRING",
                "PK4" => "INTEGER"
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);
    }

    /*     *
     * TooMuchPKInSchema
     * 测试TableMeta包含1000个PK的情况，CreateTable期望返回错误消息：The number of primary key columns must be in range: [1, 4].
     */

    public function testTooMuchPKInSchema() {
        $key = array();
        for ($i = 1; $i < 1001; $i++) {
            $key['a' . $i] = "INTEGER";
        }
        //print_r($key);die;
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test",
                "primary_key_schema" => $key,
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        try {
            $this->otsClient->createTable($tablebody);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "The number of primary key columns must be in range: [1, 4].";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * IntegerPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 INTEGER 的情况。
     */

    public function testIntegerPKInSchema() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test5",
                "primary_key_schema" => array(
                    "PK1" => "INTEGER",
                    "PK2" => "INTEGER"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        $this->assertEmpty($this->otsClient->createTable($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array(
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array(
                "PK1"=> "INTEGER",
                "PK2" => "INTEGER",
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);
    }

    /*     *
     * StringPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，类型为 STRING 的情况。
     */

    public function testStringPKInSchema() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "test5",
                "primary_key_schema" => array(
                    "PK1" => "STRING",
                    "PK2" => "STRING"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        $this->assertEmpty($this->otsClient->createTable($tablebody));
        $tablename['table_name'] = $tablebody['table_meta']['table_name'];
        $teturn = array(
            "table_name" => $tablebody['table_meta']['table_name'],
            "primary_key_schema" => array(
                "PK1"=> "STRING",
                "PK2"=> "STRING",
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);
    }

    /*     *
     * InvalidPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，
     * 类型为 DOUBLE / BOOELAN / BINARY / INF_MIN / INF_MAX 的情况，期望返回错误
     */

    public function testInvalidPKInSchema() {
        $tablebody1 = array(
            "table_meta" => array(
                "table_name" => "test",
                "primary_key_schema" => array(
                    "PK1" => "DOUBLE",
                    "PK2" => "DOUBLE"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        $tablebody2 = array(
            "table_meta" => array(
                "table_name" => "test",
                "primary_key_schema" => array(
                    "PK1" => "BOOELAN",
                    "PK2" => "BOOELAN"
                )
            ),
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                    "write" => 100,
                )
            ),
        );
        try {
            $this->otsClient->createTable($tablebody1);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "DOUBLE is an invalid type for the primary key.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        try {
            $this->otsClient->createTable($tablebody2);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSClientException $exc) {
            $c = "Column type must be one of 'INTEGER', 'STRING', 'BOOLEAN', 'DOUBLE', 'BINARY', 'INF_MIN', or 'INF_MAX'.";
            $this->assertEquals($c, $exc->getMessage());
        }
    }
    
    public function tearDown() {
        $table_name = $this->otsClient->listTable(array());
        for ($i = 0; $i < count($table_name); $i++) {
            $tablename['table_name'] = $table_name[$i];
            $this->otsClient->deleteTable($tablename);
        }
    }

}
