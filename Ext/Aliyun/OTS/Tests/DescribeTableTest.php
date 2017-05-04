<?php

namespace OTS\Tests;

use OTS;

require __DIR__ . "/../../../vendor/autoload.php";

SDKTestBase::cleanUp();

class DescribeTableTest extends SDKTestBase {

    public function setup() {
        $this->cleanUp();
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
                "PK1" => "INTEGER",
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
                "PK1" => "STRING",
                "PK2" => "STRING",
            )
        );
        $table_meta = $this->otsClient->describeTable($tablename);
        $this->assertEquals($teturn, $table_meta['table_meta']);
    }

    /*     *
     * InvalidPKInSchema
     * 测试CreateTable和DescribeTable在TableMeta包含2个PK，
     * 类型为 DOUBLE / BOOLEAN / BINARY / INF_MIN / INF_MAX 的情况，期望返回错误
     */

    public function testInvalidPKInSchema() {

        $invalidTypes = array('DOUBLE', 'BOOLEAN', 'INF_MIN', 'INF_MAX');

        foreach ($invalidTypes as $type) {
            $request = array(
                "table_meta" => array(
                    "table_name" => "test",
                    "primary_key_schema" => array(
                        "PK1" => $type,
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
                $this->otsClient->createTable($request);
                $this->fail('An expected exception has not been raised.');
            } catch (\OTS\OTSServerException $e) {
                $c = "$type is an invalid type for the primary key.";
                $this->assertEquals($c, $e->getOTSErrorMessage());
            }
        }
    }
}

