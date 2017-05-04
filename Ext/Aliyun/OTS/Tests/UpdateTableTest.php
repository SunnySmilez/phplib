<?php
namespace OTS\Tests;

use OTS;

require __DIR__ . "/../../../vendor/autoload.php";

SDKTestBase::cleanUp();
SDKTestBase::createInitialTable(
    array(
        "table_meta" => array(
            "table_name" => "myTable1",
            "primary_key_schema" => array(
                "PK1" => "INTEGER",
                "PK2" => "STRING",
            )
        ),
        "reserved_throughput" => array(
            "capacity_unit" => array(
                "read" => 10,
                "write" => 20,
            )
        ),
    )
);
SDKTestBase::createInitialTable(
    array(
        "table_meta" => array(
            "table_name" => "myTable2",
            "primary_key_schema" => array(
                "PK1" => "INTEGER",
                "PK2" => "STRING",
            )
        ),
        "reserved_throughput" => array(
            "capacity_unit" => array(
                "read" => 10,
                "write" => 20,
            )
        ),
    )
);
SDKTestBase::createInitialTable(
    array(
        "table_meta" => array(
            "table_name" => "myTable3",
            "primary_key_schema" => array(
                "PK1" => "INTEGER",
                "PK2" => "STRING",
            )
        ),
        "reserved_throughput" => array(
            "capacity_unit" => array(
                "read" => 10,
                "write" => 20,
            )
        ),
    )
);
SDKTestBase::waitForCUAdjustmentInterval();


class UpdateTableTest extends SDKTestBase {

    /* *
     * 
     * UpdateTable
     * 创建一个表，CU为（10，20），UpdateTable指定CU为（5，30），DescribeTable期望返回CU为(5, 30)。
     */
    public function testUpdateTable() {
        $name['table_name'] = "myTable1";
        $tablename = array(
            "table_name" => "myTable1",
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 5,
                    "write" => 30,
                )
            ),
        );
        $this->otsClient->updateTable($tablename);
        
        $capacity_unit = $this->otsClient->describeTable($name);
        $this->assertEquals($capacity_unit['capacity_unit_details']['capacity_unit'], $tablename['reserved_throughput']['capacity_unit']);

    }
    
    /* *
     * CUReadOnly
     * 只更新 Read CU，DescribeTable 校验返回符合预期。
     */
    public function testCUReadOnly() {
        $name['table_name'] = "myTable2";
        $tablename = array(
            "table_name" => "myTable2",
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "read" => 100,
                )
            ),
        );
        $this->otsClient->updateTable($tablename);
        
        $capacity_unit = $this->otsClient->describeTable($name);
        $this->assertEquals($capacity_unit['capacity_unit_details']['capacity_unit']['read'], 100);
        $this->assertEquals($capacity_unit['capacity_unit_details']['capacity_unit']['write'], 20);
    }
    /* *
     * CUWriteOnly
     * 只更新 Write CU，DescribeTable 校验返回符合预期。
     */
    public function testCUWriteOnly() {
        $name['table_name'] = "myTable3";
        $tablename = array(
            "table_name" => "myTable3",
            "reserved_throughput" => array(
                "capacity_unit" => array(
                    "write" => 300,
                )
            ),
        );
        $this->otsClient->updateTable($tablename);
        $capacity_unit = $this->otsClient->describeTable($name);
        $this->assertEquals($capacity_unit['capacity_unit_details']['capacity_unit']['read'], 10);
        $this->assertEquals($capacity_unit['capacity_unit_details']['capacity_unit']['write'], 300);
    }
}

