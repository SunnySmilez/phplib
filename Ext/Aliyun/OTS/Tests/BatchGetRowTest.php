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


class BatchGetRowTest extends SDKTestBase {

    public function testmes(){
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("attr1" => 1, "attr2" => "aa", "attr3" => "tas", "attr4" => 11)
        );
        $this->otsClient->putRow($tablename);
    }
    /*     *
     * 
     * EmptyBatchGetRow
     * BatchGetRow没有包含任何表的情况。
     */

    public function testEmptyBatchGetRow() {
        $batchGet = array(
        );
        try {
            $this->otsClient->batchGetRow($batchGet);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No row specified in the request of BatchGetRow.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
    }

    /*     *
     * 
     * EmptyBatchGetRow
     * BatchGetRow没有包含任何表的情况。
     */

    public function testEmpty1BatchGetRow() {
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'test8',
                ),
                array(
                    "table_name" => 'test9',
                ),
            ),
        );
        // print_r();die;
        try {
            $this->otsClient->batchGetRow($batchGet);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'test8'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 
     * 4ItemInBatchGetRow
     * BatchGetRow包含4个行。
     */

    public function testItemInBatchGetRow() {
        for ($i = 1; $i < 10; $i++) {
            $tablename = array(
                "table_name" => "myTable",
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a".$i),
                "attribute_columns" => array("attr1" => $i, "attr2" => "a".$i)
            );
            $this->otsClient->putRow($tablename);
        }
        
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        ),
                        array(
                            "primary_key" => array("PK1" => 2, "PK2" => "a2")
                        ),
                        array(
                            "primary_key" => array("PK1" => 3, "PK2" => "a3")
                        ),
                        array(
                            "primary_key" => array("PK1" => 4, "PK2" => "a4")
                        ),
                    )
                ),
            ),
        );

        $getrow = $this->otsClient->batchGetRow($batchGet);
        for ($i = 0; $i < count($batchGet['tables'][0]['rows']); $i++) {
            $this->assertEquals($getrow['tables'][0]['rows'][$i]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][$i]['primary_key']);
        }
        //print_r($getrow);die;
    }

    /**
     * 
     * EmptyTableInBatchGetRow
     * BatchGetRow包含2个表，其中有1个表有1行，另外一个表为空的情况。抛出异常
     */

    public function testEmptyTableInBatchGetRow() {
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        )
                    )
                ),
                array(
                    "table_name" => 'myTable1',
                ),
            ),
        );
        try {
            $this->otsClient->batchGetRow($batchGet);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "No row specified in table: 'myTable1'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /**
     * 
     * 1000ItemInBatchGetRow
     * BatchGetRow包含1000个行，期望返回服务端错误？
     */

    public function testItemIn1000BatchGetRow() {
        for ($i = 0; $i < 200; $i++) {
            $a[] = array(
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i)
            );
        }
        //print_r($a);die;
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => $a,
                )
            ),
        );
        try {
            $this->otsClient->batchGetRow($batchGet);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Rows count exceeds the upper limit";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /* *
     * 
     * OneTableOneFailInBatchGetRow
     * BatchGetRow有一个表中的一行失败的情况
     */

    public function testOneTableOneFailInBatchGetRow() {
        for ($i = 1; $i < 10; $i++) {
            $tablename = array(
                "table_name" => "myTable",
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "attribute_columns" => array("attr1" => $i, "attr2" => "a" . $i)
            );
            $this->otsClient->putRow($tablename);
        }
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        ),
                        array(
                            "primary_key" => array("PK1" => 2, "PK2" => "a2")
                        ),
                        array(
                            "primary_key" => array("PK11" => 3, "PK12" => "a3")
                        ),
                    )
                ),
            ),
        );
        if (is_array($this->otsClient->batchGetRow($batchGet))) {
            $getrow = $this->otsClient->batchGetRow($batchGet);
            //print_r($getrow);die;
            //print_r($getrow);die;
            $this->assertEquals($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals($getrow['tables'][0]['rows'][1]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][1]['primary_key']);
            $this->assertEquals($getrow['tables'][0]['rows'][2]['is_ok'],0);
            $error = array(
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
             );
            $this->assertEquals($getrow['tables'][0]['rows'][2]['error'],$error);
            //$this->sssertEquals()
        }
       
    }
    
    /**
     * OneTableTwoFailInBatchGetRow
     * BatchGetRow有一个表中的一行失败的情况
     */
    public function testOneTableTwoFailInBatchGetRow() {
        for ($i = 1; $i < 10; $i++) {
            $tablename = array(
                "table_name" => "myTable",
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "attribute_columns" => array("attr1" => $i, "attr2" => "a" . $i)
            );
            $this->otsClient->putRow($tablename);
        }
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        ),
                        array(
                            "primary_key" => array("PK11" => 2, "PK22" => "a2")
                        ),
                        array(
                            "primary_key" => array("PK11" => 3, "PK12" => "a3")
                        ),
                    )
                ),
            ),
        );
        if (is_array($this->otsClient->batchGetRow($batchGet))) {
            $getrow = $this->otsClient->batchGetRow($batchGet);
            //print_r($getrow);die;
            //print_r($getrow);die;
            $this->assertEquals($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals($getrow['tables'][0]['rows'][1]['is_ok'],0);
            $this->assertEquals($getrow['tables'][0]['rows'][2]['is_ok'],0);
            $error = array(
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
            );
            $this->assertEquals($getrow['tables'][0]['rows'][1]['error'],$error);
            $this->assertEquals($getrow['tables'][0]['rows'][2]['error'],$error);
            //$this->sssertEquals()
        }
       
    }

     /* *
     * 
     * TwoTableOneFailInBatchGetRow
     * BatchGetRow有2个表各有1行失败的情况
     */

    public function testTwoTableOneFailInBatchGetRow() {
        for ($i = 1; $i < 10; $i++) {
            $tablename = array(
                "table_name" => "myTable",
                "condition" => "IGNORE",
                "primary_key" => array("PK1" => $i, "PK2" => "a" . $i),
                "attribute_columns" => array("attr1" => $i, "attr2" => "a" . $i)
            );
            $this->otsClient->putRow($tablename);
        }
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
        $table = array(
            "table_name" => "myTable1",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("attr1" => 1, "attr2" => "a1")
        );
        $this->waitForTableReady();
        $this->otsClient->putRow($table);
        $batchGet = array(
            "tables" => array(
                array(
                    "table_name" => 'myTable',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        ),
                        array(
                            "primary_key" => array("PK11" => 2, "PK22" => "a2")
                        ),
                    )
                ),
                array(
                    "table_name" => 'myTable1',
                    "columns_to_get" => array(),
                    "rows" => array(
                        array(
                            "primary_key" => array("PK1" => 1, "PK2" => "a1")
                        ),
                        array(
                            "primary_key" => array("PK11" => 2, "PK22" => "a2")
                        ),
                    )
                ),
            ),
        );
        if (is_array($this->otsClient->batchGetRow($batchGet))) {
            $error = array(
                "code" => "OTSInvalidPK",
                "message" => "Primary key schema mismatch."
            );
            $getrow = $this->otsClient->batchGetRow($batchGet);
            //print_r($getrow);die;
            $this->assertEquals($getrow['tables'][0]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals($getrow['tables'][0]['rows'][1]['is_ok'],0);
            $this->assertEquals($getrow['tables'][0]['rows'][1]['error'],$error);
            $this->assertEquals($getrow['tables'][1]['rows'][0]['row']['primary_key_columns'], $batchGet['tables'][0]['rows'][0]['primary_key']);
            $this->assertEquals($getrow['tables'][1]['rows'][1]['is_ok'],0);
            $this->assertEquals($getrow['tables'][1]['rows'][1]['error'],$error);
            
            //$this->sssertEquals()
        }
       
    }
}

