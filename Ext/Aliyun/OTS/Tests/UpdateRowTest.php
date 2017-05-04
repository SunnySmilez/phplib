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

class UpdateRowTest extends SDKTestBase {

    /*     *
     * 
     * PutOnlyInUpdateRow
     * UpdateRow包含4个属性列的put操作的情况。
     */

    public function testPutOnlyInUpdateRow() {
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns_to_put" => array(
                "att1" => 'Zhon',
                "att2" => 256,
                "att3" => "cc",
                "att4" => 123
            ),
        );
        $this->otsClient->updateRow($updateRow);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        
        //print_r($updateRow['attribute_columns_to_put']);
        //print_r($getrow);
        //die;
        $this->assertEquals($getrow['row']['attribute_columns'], $updateRow['attribute_columns_to_put']);
    }

    /*     *
     * DeleteOnlyInUpdateRow
     * UpdateRow包含4个属性列的delete操作的情况。
     */

    public function testDeleteOnlyInUpdateRow() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("test1" => "name1", "test2" => 256, "test3" => "name2", "test4" => "name3")
        );
        $this->otsClient->putRow($tablename);
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns_to_delete" => array("att1", "att2", "att3", "att4"),
        );
        $this->otsClient->updateRow($updateRow);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        //print_r($getrow);die;
        $this->assertArrayNotHasKey("att1", $getrow['row']['attribute_columns']);
        $this->assertArrayNotHasKey("att2", $getrow['row']['attribute_columns']);
        $this->assertArrayNotHasKey("att3", $getrow['row']['attribute_columns']);
        $this->assertArrayNotHasKey("att4", $getrow['row']['attribute_columns']);
    }

    /*     *
     * EmptyUpdateRow
     * UpdateRow没有包含任何操作的情况
     */

    public function testEmptyUpdateRow() {
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
            "attribute_columns_to_put" => array(),
            "attribute_columns_to_put" => array()
        );
        try {
            $this->otsClient->updateRow($updateRow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No column specified while updating row.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 4PutAnd4DeleteInUpdateRow
     * UpdateRow中包含4个put操作和4个delete操作的情况。
     */

    public function testPutAndDelete4InUpdateRow() {

        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
            "attribute_columns_to_put" => array(
                "att5" => "cc",
                "att6" => "Zhon",
                "att7" => 1,
                "att8" => 123
            ),
            "attribute_columns_to_delete" => array("att1", "att2", "att3", "att4"),
        );
        $this->otsClient->updateRow($updateRow);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
        );
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($updateRow['attribute_columns_to_put'], $getrow['row']['attribute_columns']);
        //$getrowlist = $this->otsClient->getRow($body);
    }

    /*     *
     * DuplicateDeleteInUpdateRow
     * UpdateRow中包含2个delete操作列名相同的情况，期望返回服务端错误  Duplicated attribute column name: 'att1' while updating row.  
     */

    public function testDuplicateDeleteInUpdateRow() {
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
            "attribute_columns_to_put" => array(
                "att1" => "cc",
            ),
            "attribute_columns_to_delete" => array("att1"),
        );
        try {
            $this->otsClient->updateRow($updateRow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Duplicated attribute column name: 'att1' while updating row.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * PutAndDelete1000InUpdateRow
     * UpdateRow中包含1000个put和1000个delete的情况，期望返回服务端错误 The number of columns from the request exceeded the limit.
     */

    public function testPutAndDelete1000InUpdateRow() {
        for ($i = 1; $i < 1001; $i++) {
            $put['a' . $i] = "cc" . $i;
            $delete[] = 'aa' . $i;
        }
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
            "attribute_columns_to_put" => $put,
            "attribute_columns_to_delete" => $delete,
        );
        try {
            $this->otsClient->updateRow($updateRow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "The number of columns from the request exceeded the limit.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 上面测试已近包含
     * IgnoreConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为IGNORE，期望操作成功。
     * IgnoreConditionWhenRowExist
     * 测试行不存在的条件下，写操作的Condition为IGNORE，期望操作成功。
     */

//============================================================================//
    /*     *
     * ExpectExistConditionWhenRowNotExist
     * 测试行不存在的条件下，写操作的Condition为EXPECT_EXIST，期望服务端返回 Invalid Condition。
     */

    public function testExpectExistConditionWhenRowNotExist() {
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_EXIST",
            "primary_key" => array("PK1" => 30, "PK2" => "a30"),
            "attribute_columns_to_put" => array(
                "att1" => "cc",
            ),
            "attribute_columns_to_delete" => array("att2"),
        );
        //print_r($this->otsClient->updateRow($updateRow));die;
        try {
            $this->otsClient->updateRow($updateRow);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Condition check failed.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * ExpectExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_EXIST，期望操作成功。
     */

    public function testExpectExistConditionWhenRowExist() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 100, "PK2" => "a100"),
            "attribute_columns" => array("test1" => "name1", "test2" => 256, "test3" => "name2", "test4" => "name3")
        );
        $this->otsClient->putRow($tablename);
        $updateRow = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_EXIST",
            "primary_key" => array("PK1" => 100, "PK2" => "a100"),
            "attribute_columns_to_put" => array(
                "test1" => "cc",
            ),
            "attribute_columns_to_delete" => array("att2"),
        );
        $this->otsClient->updateRow($updateRow);
        //print_r($a);die;
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 100, "PK2" => "a100"),
            "columns_to_get" => array(),
        );
        $c = $this->otsClient->getRow($body);
        $this->assertEquals($c['row']['attribute_columns']['test1'], $updateRow['attribute_columns_to_put']['test1']);
    }

}

