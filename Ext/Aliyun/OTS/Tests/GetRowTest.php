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
    )
);
SDKTestBase::waitForTableReady();

SDKTestBase::putInitialData(
    array(
        "table_name" => "myTable",
        "condition" => "IGNORE",
        "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
        "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
    )
);



class GetRowTest extends SDKTestBase {

    /*     *
     * 
     * GetRowWithDefaultColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet参数为4个属性列，期望读出所有4个属性列。
     */

    public function testGetRowWith4AttributeColumnsToGet() {

        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "columns_to_get" => array("attr1", "attr2", "attr3", "attr4"),
        );
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
        $getrow = $this->otsClient->getRow($body);
        $this->assertEmpty($getrow['row']['primary_key_columns']);
        $this->assertEquals($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals($getrow['row']['attribute_columns']['attr2'], "aa");
        $this->assertEquals($getrow['row']['attribute_columns']['attr3'], "tas");
        $this->assertEquals($getrow['row']['attribute_columns']['attr4'], 11);
    }
    
    /*     *
     * 
     * GetRowWithDefaultColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求不设置ColumnsToGet，期望读出所有4个主键列和4个属性列。
     */

    public function testGetRowWithDefaultColumnsToGet() {

        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
        );
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['primary_key_columns']['PK1'], "a1");
        $this->assertEquals($getrow['row']['primary_key_columns']['PK2'], 1);
        $this->assertEquals($getrow['row']['primary_key_columns']['PK3'], "a11");
        $this->assertEquals($getrow['row']['primary_key_columns']['PK4'], 11);
        $this->assertEquals($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals($getrow['row']['attribute_columns']['attr2'], "aa");
        $this->assertEquals($getrow['row']['attribute_columns']['attr3'], "tas");
        $this->assertEquals($getrow['row']['attribute_columns']['attr4'], 11);
    }


    /*     *
     * GetRowWith0ColumsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet为空数组，期望读出所有数据。
     */

    public function testGetRowWith0ColumsToGet() {
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "columns_to_get" => array(),
        );
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['primary_key_columns'], $tablename['primary_key']);
        $this->assertEquals($getrow['row']['attribute_columns'], $tablename['attribute_columns']);

    }

    /*     *
     * GetRowWith4ColumnsToGet
     * 先PutRow包含4个主键列，4个属性列，然后GetRow请求ColumnsToGet包含其中2个主键列，2个属性列，期望返回数据包含参数中指定的列。
     */

    public function testGetRowWith4ColumnsToGet() {
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "columns_to_get" => array("PK1", "PK2", "attr1", "attr2"),
        );
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
        $getrow = $this->otsClient->getRow($body);
        $this->assertArrayHasKey("PK1", $getrow['row']['primary_key_columns']);
        $this->assertArrayHasKey("PK2", $getrow['row']['primary_key_columns']);
        $this->assertArrayHasKey("attr1", $getrow['row']['attribute_columns']);
        $this->assertArrayHasKey("attr2", $getrow['row']['attribute_columns']);
    }

    /*     *
     * GetRowWith1000ColumnsToGet
     * GetRow请求ColumnsToGet包含1000个不重复的列名，期望返回服务端错误
     */

    public function testGetRowWith1000ColumnsToGet() {
        for ($a = 1; $a < 1000; $a++) {
            $b[] = 'a' . $a;
        }
        //echo $b;
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "columns_to_get" => $b,
        );

        $this->otsClient->getRow($body);
    }

    /*     *
     * GetRowWithDuplicateColumnsToGet
     * GetRow请求ColumnsToGet包含2个重复的列名,成功返回这一列的值
     */

    public function testGetRowWithDuplicateColumnsToGet() {
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "columns_to_get" => array("PK1", "PK1"),
        );
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => "a1", "PK2" => 1, "PK3" => "a11", "PK4" => 11),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
        $getrow = $this->otsClient->getRow($body);
        // if (is_array($getrow)) {
        //print_r($getrow);die;
        $this->assertEquals($getrow['row']['primary_key_columns']["PK1"], $body['primary_key']['PK1']);
        // }
    }
}

