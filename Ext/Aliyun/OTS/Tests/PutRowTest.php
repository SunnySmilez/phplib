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
                "read" => 100,
                "write" => 100,
            )
        ),
    )
);
SDKTestBase::waitForTableReady();


class PutRowTest extends SDKTestBase {

    /*     *
     * 
     * TableNameOfZeroLength
     * 表名长度为0的情况，期望返回错误消息：Invalid table name: '{TableName}'. 中包含的TableName与输入一致。
     */

    public function testTableNameOfZeroLength() {
        $tablename1 = array(
            "table_name" => "",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("attr1" => "name", "attr2" => 256)
        );
        try {
            $this->otsClient->putRow($tablename1);
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: ''.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        //print_r($this->otsClient->putRow($tablename));
        //die;
        $tablename2 = array(
            "table_name" => "testU+0053",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("attr1" => "name", "attr2" => 256)
        );
        try {
            $this->otsClient->putRow($tablename2);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid table name: 'testU+0053'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * ColumnNameOfZeroLength
     * 列名长度为0的情况，期望返回错误消息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致
     * ColumnNameWithUnicode
     * 列名包含Unicode，期望返回错误信息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致。
     * 1KBColumnName
     * 列名包含Unicode，期望返回错误信息：Invalid column name: '{ColumnName}'. 中包含的ColumnName与输入一致。
     */

    public function testColumnNameLength() {
        //ColumnNameOfZeroLength
        $tablename1 = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("" => "name", "attr2" => 256)
        );
        try {
            $this->otsClient->putRow($tablename1);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid column name: ''.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        //ColumnNameWithUnicode
        $tablename2 = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("#name" => "name", "attr2" => 256)
        );
        try {
            $this->otsClient->putRow($tablename2);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid column name: '#name'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        //1KBColumnName
        $name = "";
        for ($i = 1; $i < 1025; $i++) {
            $name .="a";
        }
        $tablename3 = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("{$name}" => "name", "attr2" => 256)
        );
        try {
            $this->otsClient->putRow($tablename3);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "Invalid column name: '{$name}'.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /*     *
     * 10WriteCUConsumed
     * 测试接口消耗10个写CU时返回的CU Consumed符合预期。
     */

    public function testWrite10CUConsumed() {
        $name = "";
        for ($i = 1; $i < (4097*9); $i++) {
            $name .="a";
        }
        $tablename = array(
            "table_name" => "myTable1",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 1, "PK2" => "a1"),
            "attribute_columns" => array("att2" => $name)
        );

        if (is_array($this->otsClient->putRow($tablename))) {
            $name = $this->otsClient->putRow($tablename);
            $this->assertEquals($name['consumed']['capacity_unit']['write'], 10);
            $this->assertEquals($name['consumed']['capacity_unit']['read'], 0);
        }
        //print_r($name['consumed']['capacity_unit']['write']);die;
        //$getrow = $this->otsClient->putRow($tablename);
    }

    /*     *
     * 测试不同类型的列值
     * NormanStringValue
     * 测试StringValue为10个字节的情况。
     * UnicodeStringValue
     * 测试StringValue包含Unicode字符的情况。
     */

    public function testNormanStringValue() {
        $name = "";
        for ($i = 1; $i < (1025 * 10); $i++) {
            $name .="a";
        }
        //echo strlen($a);die;
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 11, "PK2" => "a11"),
            "attribute_columns" => array("att2" => $name)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array('PK1' => 11, 'PK2' => 'a11'),
            "columns_to_get" => array(),
        );

        if (is_array($this->otsClient->getRow($body))) {
            $name = $this->otsClient->getRow($body);
            $this->assertEquals($name['row']['attribute_columns']['att2'], $tablename['attribute_columns']['att2']);
        }
        //print_r($name);die;
        //$getrow = $this->otsClient->putRow($tablename);
    //
    }

    /*     *
     * 测试不同类型的列值
     * UnicodeStringValue
     * 测试StringValue包含Unicode字符的情况。
     * EmptyStringValue
     * 测试空字符串的情况。
     */

    public function testUnicodeStringValue() {
        //echo strlen($a);die;
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 12, "PK2" => "a12"),
            "attribute_columns" => array("att1" => "sdfv\u597d", "att2" => "U+0053")
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array('PK1' => 12, 'PK2' => 'a12'),
        );

        if (is_array($this->otsClient->getRow($body))) {
            $name = $this->otsClient->getRow($body);
            //UnicodeStringValue
            $this->assertEquals($name['row']['attribute_columns'], $tablename['attribute_columns']);
        }
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 13, "PK2" => "a13"),
            "attribute_columns" => array("att1" => "", "att2" => "")
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array('PK1' => 13, 'PK2' => 'a13'),
        );

        if (is_array($this->otsClient->getRow($body))) {
            $name = $this->otsClient->getRow($body);
            //UnicodeStringValue
            $this->assertEmpty($name['row']['attribute_columns']['att1']);
            $this->assertEmpty($name['row']['attribute_columns']['att2']);
        }
        //print_r($name);die;
        //$getrow = $this->otsClient->putRow($tablename);
    //
    }

    /*     *
     * StringValueTooLong
     * 测试字符串长度为1MB的情况，期望返回错误消息 最长65536。
     */

    public function testStringValueTooLong() {
        $name = "";
        for ($i = 1; $i < (1025 * 1024); $i++) {
            $name .="a";
        }
        //echo strlen($a);die;
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 20, "PK2" => "a20"),
            "attribute_columns" => array("att1" => $name)
        );
        try {
            $this->otsClient->putRow($tablename);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $c = "The length of attribute column: 'att1' exceeded the MaxLength:65536 with CurrentLength:1049599.";
            $this->assertEquals($c, $exc->getOTSErrorMessage());
        }
        
    }

    /* *
     * CASE_ID: NormalIntegerValue
     * 测试IntegerValue值为10的情况。
     * CASE_ID: IntegerValueInBoundary
     * 测试IntegerValue的值为8位有符号整数的最小值或最大值的情况 
     * 负8位整数getRow获取的值为4293856185
     * 正8位整数最大4293856185最下为0
     */

    public function testIntegerValue() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 30, "PK2" => "a30"),
            "attribute_columns" => array("attr10" => -10)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 30, "PK2" => "a30"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['attribute_columns']['attr10'], -10);

        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 31, "PK2" => "a31"),
            "attribute_columns" => array("attr1" => 1, "attr2" => 0, "attr3" => 4293856185)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 31, "PK2" => "a31"),
            "columns_to_get" => array(),
        );
        $getrow1 = $this->otsClient->getRow($body);
        //echo $getrow['row']['attribute_columns']['attr1'];die;
        $this->assertEquals($getrow1['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals($getrow1['row']['attribute_columns']['attr2'], 0);
        $this->assertEquals($getrow1['row']['attribute_columns']['attr3'], 4293856185);
    }

    /*     *
     * NormalDoubleValue
     * 测试DoubleValue值为3.1415926的情况。
     * DoubleValueInBoundary
     * 测试DoubleValue的值为8位有符号浮点数的最小值或最大值的情况
     */

    public function testDoubleValue() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 40, "PK2" => "a40"),
            "attribute_columns" => array("attr10" => 3.1415926)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 40, "PK2" => "a40"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['attribute_columns']['attr10'], 3.1415926);

        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 41, "PK2" => "a41"),
            "attribute_columns" => array("attr11" => -0.0000001, "attr12" => 9.9999999)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 41, "PK2" => "a41"),
            "columns_to_get" => array(),
        );
        $getrow1 = $this->otsClient->getRow($body);
        //echo $getrow1['row']['attribute_columns']['attr11'];die;
        $this->assertEquals($getrow1['row']['attribute_columns']['attr11'], -0.0000001);
        //$this->assertEquals($getrow1['row']['attribute_columns']['attr2'],0);
        $this->assertEquals($getrow1['row']['attribute_columns']['attr12'], 9.9999999);
    }

    /*     *
     * BooleanValueTrue
     * 描述：测试布尔值为True的情况。
     *  BooleanValueFalse
     * 描述：测试布尔值为False的情况
     */

    public function testBooleanValue() {
        $tablename = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
            "attribute_columns" => array("attr1" => true, "attr2" => false)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals($getrow['row']['attribute_columns']['attr2'], 0);
    }
    
    /*     *
     * ExpectNotExistConditionWhenRowNotExist
     * 描述：测试行不存在的条件下，写操作的Condition为EXPECT_NOT_EXIST。
     * ExpectNotExistConditionWhenRowExist
     * 测试行存在的条件下，写操作的Condition为EXPECT_NOT_EXIST,返回错误信息Condition check failed.
     */

    public function testExpectNotExistConditionWhenRowNotExist() {
        $request = array(
            "table_name" => "myTable",
            "condition" => "IGNORE",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
        );
        $this->otsClient->deleteRow($request);

        $tablename = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_NOT_EXIST",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
            "attribute_columns" => array("attr1" => true, "attr2" => false)
        );
        $this->otsClient->putRow($tablename);
        $body = array(
            "table_name" => "myTable",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
            "columns_to_get" => array(),
        );
        $getrow = $this->otsClient->getRow($body);
        $this->assertEquals($getrow['row']['attribute_columns']['attr1'], 1);
        $this->assertEquals($getrow['row']['attribute_columns']['attr2'], 0);
        
        $tablename1 = array(
            "table_name" => "myTable",
            "condition" => "EXPECT_NOT_EXIST",
            "primary_key" => array("PK1" => 50, "PK2" => "a50"),
            "attribute_columns" => array("attr1" => true, "attr2" => false)
        );
        
        try {
            $this->otsClient->putRow($tablename1);
            $this->fail('An expected exception has not been raised.');
        } catch (\OTS\OTSServerException $exc) {
            $a = $exc->getMessage();
            $c = "Condition check failed.";
            $this->assertContains($c, $a);
        }
    }
}

