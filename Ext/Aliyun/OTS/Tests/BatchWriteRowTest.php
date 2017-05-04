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


class BatchWriteRowTest extends SDKTestBase {

    /*     *
     * 
     * GetEmptyBatchWriteRow
     * BatchWriteRow没有包含任何表的情况
     */

    public function testGetEmptyBatchWriteRow() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'test9',
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow($batchWrite);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'test9'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * EmptyTableInBatchWriteRow
     * BatchWriteRow包含2个表，其中有1个表有1行，另外一个表为空的情况。
     */

    public function testGetRowWith0ColumsToGet() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'test9',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                ),
                array(
                    "table_name" => 'test8',
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow($batchWrite);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'test8'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * PutOnlyInBatchWriteRow
     * BatchWriteRow包含4个Put操作
     */

    public function testPutOnlyInBatchWriteRow() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name1", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                            "attribute_columns" => array("att1" => "name2", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                            "attribute_columns" => array("att1" => "name3", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                            "attribute_columns" => array("att1" => "name4", "att2" => 256)
                        ),
                    ),
                ),
            //tables
            )
        );
        $this->otsClient->batchWriteRow($batchWrite);
        for ($i = 1; $i < 5; $i++) {
            $body = array(
                "table_name" => "myTable",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "columns_to_get" => array(),
            );
            $a[] = $this->otsClient->getRow($body);
        }
        $this->assertEquals(count($a), 4);
        for ($c = 0; $c < count($a); $c++) {
            $this->assertEquals($a[$c]['row']['primary_key_columns'], $batchWrite['tables'][0]['put_rows'][$c]['primary_key']);
            $this->assertEquals($a[$c]['row']['attribute_columns'], $batchWrite['tables'][0]['put_rows'][$c]['attribute_columns']);
        }
    }

    /*     *
     * UpdateOnlyInBatchWriteRow
     * BatchWriteRow包含4个Update操作
     */

    public function testUpdateOnlyInBatchWriteRow() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name1", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                            "attribute_columns" => array("att1" => "name2", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                            "attribute_columns" => array("att1" => "name3", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                            "attribute_columns" => array("att1" => "name4", "att2" => 256)
                        ),
                    ),
                ),
            //tables
            )
        );
        $this->otsClient->batchWriteRow($batchWrite);
        $batchWrite1 = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "update_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ////////添加多行插入  put_rows
                    ),
                )
            )
        );
        $this->otsClient->batchWriteRow($batchWrite1);
        for ($i = 1; $i < 5; $i++) {
            $body = array(
                "table_name" => "myTable",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "columns_to_get" => array(),
            );
            $a[] = $this->otsClient->getRow($body);
        }
        $this->assertEquals(count($a), 4);
        for ($c = 0; $c < count($a); $c++) {
            // print_r($a[$c]['row']['primary_key_columns']);
            //print_r($batchWrite1['tables'][0]['update_rows'][0]['attribute_columns_to_put']);
            $this->assertEquals($a[$c]['row']['primary_key_columns'], $batchWrite['tables'][0]['put_rows'][$c]['primary_key']);
            $this->assertEquals($a[$c]['row']['attribute_columns'], $batchWrite1['tables'][0]['update_rows'][$c]['attribute_columns_to_put']);
        }
    }

    /*     *
     * DeleteOnlyInBatchWriteRow
     * BatchWriteRow包含4个Delete操作
     */

    public function testDeleteOnlyInBatchWriteRow() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name1", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                            "attribute_columns" => array("att1" => "name2", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                            "attribute_columns" => array("att1" => "name3", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                            "attribute_columns" => array("att1" => "name4", "att2" => 256)
                        ),
                    ),
                ),
            //tables
            )
        );
        $this->otsClient->batchWriteRow($batchWrite);
        $batchWrite1 = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                        ),
                    ////////添加多行插入  put_rows
                    ),
                )
            )
        );
        $getrow = $this->otsClient->batchWriteRow($batchWrite1);
        //print_r($getrow);die;
        for ($i = 1; $i < 5; $i++) {
            $body = array(
                "table_name" => "myTable",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "columns_to_get" => array(),
            );
            $a[] = $this->otsClient->getRow($body);
        }
        $this->assertEquals(count($a), 4);
        for ($c = 0; $c < count($a); $c++) {
            $this->assertEmpty($a[$c]['row']['primary_key_columns']);
            $this->assertEmpty($a[$c]['row']['attribute_columns']);
        }
    }

    /*     *
     * 4PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含4个Put，4个Update和4个Delete操作
     */

    public function testPutUpdateDeleteInBatchWriteRow() {
        for ($i = 1; $i < 9; $i++) {
            $put[] = array(
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "attribute_columns" => array("att1" => "name{$i}", "att2" => 256)
            );
        }
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => $put,
                ),
            //tables
            )
        );
        $this->otsClient->batchWriteRow($batchWrite);
        $batchWrite1 = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 9, "PK2" => "a9"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 10, "PK2" => "a10"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 12, "PK2" => "a12"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ////////添加多行插入  put_rows
                    ),
                    "update_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 5, "PK2" => "a5"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 6, "PK2" => "a6"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 7, "PK2" => "a7"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 8, "PK2" => "a8"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                        ),
                    ),
                )
            )
        );
        $getrow = $this->otsClient->batchWriteRow($batchWrite1);
        $getRange = array(
            "table_name" => "myTable",
            "direction" => "FORWARD",
            "columns_to_get" => array(),
            "limit" => 100,
            "inclusive_start_primary_key" => array(
                "PK1" => 1,
                "PK2" => "a1"
            ),
            "exclusive_end_primary_key" => array(
                "PK1" => 30,
                "PK2" => "a30"
            )
        );
        $a = $this->otsClient->getRange($getRange);
        $this->assertEquals(count($a['rows']), 8);
        for ($i = 0; $i < count($a['rows']); $i++) {
            $row = $a['rows'][$i];
            $pk1 = $row['primary_key_columns']['PK1'];
            $columns = $row['attribute_columns'];
            $this->assertEquals($pk1, $i + 5);
            // 1-4  rows deleted
            if ($pk1 >= 5 && $pk1 <= 8) {
                // 5-8  rows updated
                $this->assertEquals($columns['att1'], 'Zhon');
            } elseif ($pk1 >= 9 && $pk1 <= 12) {
                // 9-12 rows put
                $this->assertEquals($columns['att1'], 'name');
                $this->assertEquals($columns['att2'], 256);
            } else {
                $this->fail("Deleted rows read.");
            }
        }
    }

    /*     *
     * 1000PutUpdateDeleteInBatchWriteRow
     * BatchWriteRow同时包含1000个Put，4个Update和4个Delete操作，期望返回服务端错误
     */

    public function testPut1000UpdateDeleteInBatchWriteRow() {
        for ($i = 1; $i < 1000; $i++) {
            $a[] = array(
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "attribute_columns" => array("att1" => "name", "att2" => 256)
            );
        }
        //print_r($a);die;
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => $a,
                    "update_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 5, "PK2" => "a5"),
                            "attribute_columns" => array(
                                array("att1" => 'Zhon', "type" => "PUT"),
                                array("att2" => 256, "type" => "DELETE"),
                            ),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 6, "PK2" => "a6"),
                            "attribute_columns" => array(
                                array("att1" => 'Zhon', "type" => "PUT"),
                                array("att2" => 256, "type" => "DELETE"),
                            ),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 7, "PK2" => "a7"),
                            "attribute_columns" => array(
                                array("att1" => 'Zhon', "type" => "PUT"),
                                array("att2" => 256, "type" => "DELETE"),
                            ),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 8, "PK2" => "a8"),
                            "attribute_columns" => array(
                                array("att1" => 'Zhon', "type" => "PUT"),
                                array("att2" => 256, "type" => "DELETE"),
                            ),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 2, "PK2" => "a2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 3, "PK2" => "a3"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 4, "PK2" => "a4"),
                        ),
                    ),
                )
            )
        );
        try {
            $this->otsClient->batchWriteRow($batchWrite);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 4TablesInBatchWriteRow
     * BatchWriteRow包含4个表的情况。
     */

    public function testTables4InBatchWriteRow() {
        for ($i = 1; $i < 5; $i++) {
            $tablebody = array(
                "table_meta" => array(
                    "table_name" => "test" . $i,
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
            );
            $this->otsClient->createTable($tablebody);
        }
        $this->waitForTableReady();
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'test1',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                ),
                array(
                    "table_name" => 'test2',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                ),
                array(
                    "table_name" => 'test3',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                ),
                array(
                    "table_name" => 'test4',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                ),
            )
        );
        $this->otsClient->batchWriteRow($batchWrite);
        for ($i = 1; $i < 5; $i++) {
            $body = array(
                "table_name" => "test" . $i,
                "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                "columns_to_get" => array(),
            );
            $getrow[] = $this->otsClient->getRow($body);
        }
        $primary = array("PK1" => 1, "PK2" => "a1");
        $columns = array("att1" => "name", "att2" => 256);
        $this->assertEquals(count($getrow), 4);
        for ($i = 0; $i < count($getrow); $i++) {
            $this->assertEquals($getrow[$i]['row']['primary_key_columns'], $primary);
            $this->assertEquals($getrow[$i]['row']['attribute_columns'], $columns);
        }
    }

    /*     *
     * OneTableOneFailInBatchWriteRow
     * BatchWriteRow有一个表中的一行失败的情况
     */

    public function testOneTableOneFailInBatchWriteRow() {
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 9, "PK2" => "a9"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 10, "PK2" => "a10"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ////////添加多行插入  put_rows
                    ),
                    "update_rows" => array(
                        array(
                            "condition" => "EXPECT_EXIST",
                            "primary_key" => array("PK1" => 510, "PK2" => "a510"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 6, "PK2" => "a6"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 12, "PK2" => "a12"),
                        ),
                    ),
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow($batchWrite);
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['is_ok'],0);
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['error'], array("code" => "OTSConditionCheckFail", "message" => "Condition check failed."));
    }

    /*     *
     * OneTableTwoFailInBatchWriteRow
     * BatchWriteRow有一个表中的二行失败的情况
     */

    public function testOneTableTwoFailInBatchWriteRow() {

        $pkOfRows = array(
            array("PK1" => 9, "PK2" => "a9"),
            array("PK1" => 10, "PK2" => "a10"),
            array("PK1" => 510, "PK2" => "a510"),
            array("PK1" => 6, "PK2" => "a6"),
            array("PK1" => 11, "PK2" => "a11"),
            array("PK1" => 12, "PK2" => "a12"),
        );

        foreach ($pkOfRows as $pk) {
            $this->otsClient->deleteRow(array(
                "table_name" => 'myTable', 
                "condition" => "IGNORE", 
                "primary_key" => $pk
            ));
        }

        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 9, "PK2" => "a9"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 10, "PK2" => "a10"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ////////添加多行插入  put_rows
                    ),
                    "update_rows" => array(
                        array(
                            "condition" => "EXPECT_EXIST",
                            "primary_key" => array("PK1" => 510, "PK2" => "a510"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                        array(
                            "condition" => "EXPECT_EXIST",
                            "primary_key" => array("PK1" => 6, "PK2" => "a6"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
                        ),
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 12, "PK2" => "a12"),
                        ),
                    ),
                )
            )
        );
        $writerow = $this->otsClient->batchWriteRow($batchWrite);
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['is_ok'], 0);
        $this->assertEquals($writerow['tables'][0]['update_rows'][1]['is_ok'], 0);
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['error'], array("code" => "OTSConditionCheckFail", "message" => "Condition check failed."));
        $this->assertEquals($writerow['tables'][0]['update_rows'][1]['error'], array("code" => "OTSConditionCheckFail", "message" => "Condition check failed."));
    }
    
    /*     *
     * TwoTableOneFailInBatchWriteRow
     * BatchWriteRow有2个表各有1行失败的情况
     */

    public function testTwoTableOneFailInBatchWriteRow() {
        $tablebody = array(
            "table_meta" => array(
                "table_name" => "myTable1",
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
        );
        $this->otsClient->createTable($tablebody);
        $this->waitForTableReady();
        $batchWrite = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 9, "PK2" => "a9"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ////////添加多行插入  put_rows
                    ),
                    "update_rows" => array(
                        array(
                            "condition" => "EXPECT_EXIST",
                            "primary_key" => array("PK1" => 510, "PK2" => "a510"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
                        ),
                    ),
                ),
                array(
                    "table_name" => 'myTable1',
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 9, "PK2" => "a9"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ////////添加多行插入  put_rows
                    ),
                    "update_rows" => array(
                        array(
                            "condition" => "EXPECT_EXIST",
                            "primary_key" => array("PK1" => 510, "PK2" => "a510"),
                            "attribute_columns_to_put" => array("att1" => 'Zhon'),
                            "attribute_columns_to_delete" => array("att2"),
                        ),
                    ),
                    "delete_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
                        ),
                    ),
                ),
            )
        );
        $writerow = $this->otsClient->batchWriteRow($batchWrite);
        //print_r($writerow);die;
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['is_ok'],0);
        $this->assertEquals($writerow['tables'][1]['update_rows'][0]['is_ok'],0);
        $this->assertEquals($writerow['tables'][0]['update_rows'][0]['error'], array("code" => "OTSConditionCheckFail", "message" => "Condition check failed."));
        $this->assertEquals($writerow['tables'][1]['update_rows'][0]['error'], array("code" => "OTSConditionCheckFail", "message" => "Condition check failed."));
    }
    
    /* *
     * 1000TablesInBatchWriteRow
     * BatchWriteRow包含1000个表的情况，期望返回服务端错误
     */

    public function testP1000TablesInBatchWriteRow() {
        for($i=1;$i<1001;$i++){
            $res[] = array(
                    "table_name" => 'test'.$i,
                    "put_rows" => array(
                        array(
                            "condition" => "IGNORE",
                            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
                            "attribute_columns" => array("att1" => "name", "att2" => 256)
                        ),
                    ),
                );
        }
        $batchWrite = array(
            "tables" => $res,
        );
        try {
            $this->otsClient->batchWriteRow($batchWrite);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }
}

