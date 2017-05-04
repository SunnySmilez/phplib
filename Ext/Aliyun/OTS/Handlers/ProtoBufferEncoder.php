<?php
namespace OTS\Handlers;

use OTS;

use CreateTableRequest;
use ListTableRequest;
use DeleteTableRequest;
use DescribeTableRequest;
use UpdateTableRequest;
use GetRowRequest;
use PutRowRequest;
use UpdateRowRequest;
use DeleteRowRequest;
use BatchGetRowRequest;
use BatchWriteRowRequest;
use GetRangeRequest;

use ColumnType, OperationType, Condition, Column, ColumnValue, ColumnUpdate;
use Direction, ReservedThroughput, CapacityUnit;
use TableInBatchGetRowRequest, RowInBatchGetRowRequest;
use TableInBatchWriteRowRequest;
use PutRowInBatchWriteRowRequest;
use UpdateRowInBatchWriteRowRequest;
use DeleteRowInBatchWriteRowRequest;

class ProtoBufferEncoder
{
    private function checkParameter($request)
    {
        // TODO implement
    }

    private function preprocessColumnType($type)
    {
        switch ($type) {
            case 'INTEGER': return ColumnType::INTEGER;
            case 'STRING': return ColumnType::STRING;
            case 'BOOLEAN': return ColumnType::BOOLEAN;
            case 'DOUBLE': return ColumnType::DOUBLE;
            case 'BINARY': return ColumnType::BINARY;
            case 'INF_MIN': return ColumnType::INF_MIN;
            case 'INF_MAX': return ColumnType::INF_MAX;
            default:
                throw new \OTS\OTSClientException("Column type must be one of 'INTEGER', 'STRING', 'BOOLEAN', 'DOUBLE', 'BINARY', 'INF_MIN', or 'INF_MAX'.");
        }
    }

    private function preprocessColumnValue($columnValue)
    {
        if (is_bool($columnValue)) {

            // is_bool() is checked before is_int(), to avoid type upcasting
            $columnValue = array('type' => 'BOOLEAN', 'value' => $columnValue);

        } else if (is_int($columnValue)) {
            $columnValue = array('type' => 'INTEGER', 'value' => $columnValue);
        } else if (is_string($columnValue)) {
            $columnValue = array('type' => 'STRING', 'value' => $columnValue);
        } else if (is_double($columnValue) || is_float($columnValue)) {
            $columnValue = array('type' => 'DOUBLE', 'value' => $columnValue);
        } else if (is_array($columnValue)) {
            if (!isset($columnValue['type'])) {
                throw new \OTS\OTSClientException("An array column value must has 'type' field.");
            }

            if ($columnValue['type'] != 'INF_MIN' && $columnValue['type'] != 'INF_MAX' && !isset($columnValue['value'])) {
                throw new \OTS\OTSClientException("A column value wth type INTEGER, STRING, BOOLEAN, DOUBLE, or BINARY must has 'value' field.");
            }
        } else {
            throw new \OTS\OTSClientException("A column value must be a int, string, bool, double, float, or array.");
        }

        $type = $this->preprocessColumnType($columnValue['type']);
        $ret = array('type' => $type);

        switch ($type) {
            case ColumnType::INTEGER: 
                $ret['v_int'] = $columnValue['value'];
                break;
            case ColumnType::STRING: 
                $ret['v_string'] = $columnValue['value'];
                break;
            case ColumnType::BOOLEAN: 
                $ret['v_bool'] = $columnValue['value'];
                break;
            case ColumnType::DOUBLE:
                $ret['v_double'] = $columnValue['value'];
                break;
            case ColumnType::BINARY: 
                $ret['v_binary'] = $columnValue['value'];
                break;
            case ColumnType::INF_MIN:
                break;
            case ColumnType::INF_MAX:
                break;
        }

        return $ret;
    }

    private function preprocessColumns($columns)
    {
        $ret = array();

        foreach ($columns as $name => $value)
        {
            $data = array(
                'name' => $name,
                'value' => $this->preprocessColumnValue($value),
            );
            array_push($ret, $data);
        }

        return $ret;
    }

    private function preprocessCondition($condition)
    {
        switch ($condition) {
            case 'IGNORE':
                return \RowExistenceExpectation::IGNORE;
            case 'EXPECT_EXIST':
                return \RowExistenceExpectation::EXPECT_EXIST;
            case 'EXPECT_NOT_EXIST':
                return \RowExistenceExpectation::EXPECT_NOT_EXIST;
            default:
                throw new \OTS\OTSClientException("Condition must be one of 'IGNORE', 'EXPECT_EXIST' or 'EXPECT_NOT_EXIST'.");
        }
    }

    private function preprocessDeleteRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition']['row_existence'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
        return $ret;
    }

    private function preprocessCreateTableRequest($request)
    {
        $ret = array();
        $ret['table_meta']['table_name'] = $request['table_meta']['table_name'];
        $ret['reserved_throughput'] = $request['reserved_throughput'];
        foreach ($request['table_meta']['primary_key_schema'] as $k => $v) {
            $name[] = $k;
            $type[] = $this->preprocessColumnType($v);
        }
        for ($i = 0; $i < count($request['table_meta']['primary_key_schema']); $i++) {
            $ret['table_meta']['primary_key_schema'][$i]['name'] = $name[$i];
            $ret['table_meta']['primary_key_schema'][$i]['type'] = $type[$i];
        }
        return $ret;
    }

    private function preprocessPutRowRequest($request)
    {
        // FIXME handle BINARY type
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition'] = array();
        $ret['condition']['row_existence'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
     
        if (!isset($request['attribute_columns'])) {
            $request['attribute_columns'] = array();
        }

        $ret['attribute_columns'] = $this->preprocessColumns($request['attribute_columns']);
        return $ret;
    }

    private function preprocessGetRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);
        if (!isset($request['columns_to_get'])) {
            $ret['columns_to_get'] = array();
        } else {
            $ret['columns_to_get'] = $request['columns_to_get'];
        }
        return $ret;
    }

    private function preprocessPutInUpdateRowRequest($columnsToPut)
    {
        $ret = array();
        foreach($columnsToPut as $name => $value) {
            $columnData = array(
                'type' => OperationType::PUT,
                'name' => $name,
                'value' => $this->preprocessColumnValue($value),
            );
            array_push($ret, $columnData);
        }
        return $ret;
    }

    private function preprocessDeleteInUpdateRowRequest($columnsToDelete)
    {
        $ret = array();
        foreach ($columnsToDelete as $columnName) {
            array_push($ret, array(
                'type' => OperationType::DELETE,
                'name' => $columnName,
            ));
        }
        return $ret;
    }
    
    private function preprocessUpdateRowRequest($request)
    {
        $ret = array();
        $ret['table_name'] = $request['table_name'];
        $ret['condition']['row_existence'] = $this->preprocessCondition($request['condition']);
        $ret['primary_key'] = $this->preprocessColumns($request['primary_key']);

        $attributeColumns = array();

        if (!empty($request['attribute_columns_to_put'])) {
            $columnsToPut = $this->preprocessPutInUpdateRowRequest($request['attribute_columns_to_put']);
            $attributeColumns = array_merge($attributeColumns, $columnsToPut);
        }

        if (!empty($request['attribute_columns_to_delete'])) {
            $columnsToDelete = $this->preprocessDeleteInUpdateRowRequest($request['attribute_columns_to_delete']);
            $attributeColumns = array_merge($attributeColumns, $columnsToDelete);
        }

        $ret['attribute_columns'] = $attributeColumns;
        return $ret;
    }

    private function preprocessGetRangeRequest($request)
    {
        $ret = array();

        $ret['table_name'] = $request['table_name'];
        switch ($request['direction']) {
            case 'FORWARD':
                $ret['direction'] = Direction::FORWARD;
                break;
            case 'BACKWARD':
                $ret['direction'] = Direction::BACKWARD;
                break;
            default:
                throw new \OTS\OTSClientException("GetRange direction must be 'FORWARD' or 'BACKWARD'.");
        }
     
     
        if (isset($request['columns_to_get'])) {
            $ret['columns_to_get'] = $request['columns_to_get'];
        } else {
            $ret['columns_to_get'] = array();
        }

        if (isset($request['limit'])) {
            $ret['limit'] = $request['limit'];
        }
        $ret['inclusive_start_primary_key'] = $this->preprocessColumns($request['inclusive_start_primary_key']);
        $ret['exclusive_end_primary_key'] = $this->preprocessColumns($request['exclusive_end_primary_key']);
        return $ret;
    }

    private function preprocessBatchGetRowRequest($request)
    {
        $ret = array();
        if (!empty($request['tables'])) {
            for ($i = 0; $i < count($request['tables']); $i++) {
                $ret['tables'][$i]['table_name'] = $request['tables'][$i]['table_name'];
                if (!empty($request['tables'][$i]['columns_to_get'])) {
                    $ret['tables'][$i]['columns_to_get'] = $request['tables'][$i]['columns_to_get'];
                }
                if (!empty($request['tables'][$i]['rows'])) {
                    for ($j = 0; $j < count($request['tables'][$i]['rows']); $j++) {
                        $ret['tables'][$i]['rows'][$j]['primary_key'] = $this->preprocessColumns($request['tables'][$i]['rows'][$j]['primary_key']);
                    }
                }
            }
        }

        return $ret;
    }

    private function preprocessBatchWriteRowRequest($request)
    {
        $ret = array();
        for ($i = 0; $i < count($request['tables']); $i++) {
            $ret['tables'][$i]['table_name'] = $request['tables'][$i]['table_name'];
            if (!empty($request['tables'][$i]['put_rows'])) {
                for ($a = 0; $a < count($request['tables'][$i]['put_rows']); $a++) {
                    $request['tables'][$i]['put_rows'][$a]['table_name'] = "";
                    $ret['tables'][$i]['put_rows'][$a] = $this->preprocessPutRowRequest($request['tables'][$i]['put_rows'][$a]);
                    unset($ret['tables'][$i]['put_rows'][$a]['table_name']);
                }
            }
            if (!empty($request['tables'][$i]['update_rows'])) {
                for ($b = 0; $b < count($request['tables'][$i]['update_rows']); $b++) {
                    $request['tables'][$i]['update_rows'][$b]['table_name'] = "";
                    $ret['tables'][$i]['update_rows'][$b] = $this->preprocessUpdateRowRequest($request['tables'][$i]['update_rows'][$b]);
                    unset($ret['tables'][$i]['update_rows'][$b]['table_name']);
                }
            }
            if (!empty($request['tables'][$i]['delete_rows'])) {
                for ($c = 0; $c < count($request['tables'][$i]['delete_rows']); $c++) {
                    $request['tables'][$i]['delete_rows'][$c]['table_name'] = "";
                    $ret['tables'][$i]['delete_rows'][$c] = $this->preprocessDeleteRowRequest($request['tables'][$i]['delete_rows'][$c]);
                    unset($ret['tables'][$i]['delete_rows'][$c]['table_name']);
                }
            }
        }
        return $ret;
    }

    private function encodeListTableRequest($request)
    {
        return "";
    }
    
    private function encodeDeleteTableRequest($request)
    {
        $pbMessage = new DeleteTableRequest();
        $pbMessage->set_table_name($request["table_name"]);
                                          
        return $pbMessage->SerializeToString();
    }

    private function encodeDescribeTableRequest($request)
    {
        $pbMessage = new DescribeTableRequest();
        $pbMessage->set_table_name($request["table_name"]);
                                          
        return $pbMessage->SerializeToString();
    }

    private function encodeUpdateTableRequest($request)
    {
        $pbMessage = new UpdateTableRequest();
        $reservedThroughput = new ReservedThroughput();
        $capacityUnit = new CapacityUnit();
        if(!empty($request['reserved_throughput']['capacity_unit']['read'])){
            $capacityUnit->set_read($request['reserved_throughput']['capacity_unit']['read']);
        }
        if(!empty($request['reserved_throughput']['capacity_unit']['write'])){
            $capacityUnit->set_write($request['reserved_throughput']['capacity_unit']['write']);
        }
        $reservedThroughput->set_capacity_unit($capacityUnit);
                 
        $pbMessage->set_table_name($request['table_name']);
        $pbMessage->set_reserved_throughput($reservedThroughput);
         
        return $pbMessage->SerializeToString();
    }

    private function encodeCreateTableRequest($request)
    {
        $pbMessage = new \CreateTableRequest();
        $tableMeta = new \TableMeta();
        $tableName = $tableMeta->set_table_name($request['table_meta']['table_name']);
        if (!empty($request['table_meta']['primary_key_schema']))
        {
            for ($i=0; $i < count($request['table_meta']['primary_key_schema']); $i++)
            {
                $columnSchema = new \ColumnSchema();
                $columnSchema->set_name($request['table_meta']['primary_key_schema'][$i]['name']);
                $columnSchema->set_type($request['table_meta']['primary_key_schema'][$i]['type']);
                $tableMeta->set_primary_key($i, $columnSchema);
            }
        }
         
        $reservedThroughput = new \ReservedThroughput();
        $capacityUnit = new \CapacityUnit();
        $capacityUnit->set_read($request['reserved_throughput']['capacity_unit']['read']);
        $capacityUnit->set_write($request['reserved_throughput']['capacity_unit']['write']);
        $reservedThroughput->set_capacity_unit($capacityUnit);
         
        $pbMessage->set_table_meta($tableMeta);
        $pbMessage->set_reserved_throughput($reservedThroughput);
         
        return $pbMessage->SerializeToString();
    }

    private function encodeGetRowRequest($request)
    {
        $pbMessage = new GetRowRequest();
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->set_columns_to_get($i, $request['columns_to_get'][$i]);
            }
        }
         
        $pbMessage->set_table_name($request['table_name']);
        return $pbMessage->SerializeToString();
    }

    private function encodePutRowRequest($request)
    {
        $pbMessage = new PutRowRequest();
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['attribute_columns']))
        {
            for ($i=0; $i < count($request['attribute_columns']); $i++)
            {
                $attributeColumn = new Column();
                $columnValue = new ColumnValue();
                $attributeColumn->set_name($request['attribute_columns'][$i]['name']);
                $columnValue->set_type($request['attribute_columns'][$i]['value']['type']);
                switch ($request['attribute_columns'][$i]['value']['type'])
                {
                    case ColumnType::INTEGER:
                        $columnValue->set_v_int($request['attribute_columns'][$i]['value']['v_int']);
                        break;  
                    case ColumnType::STRING:
                        $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                        break;
                    case ColumnType::BOOLEAN:
                        $columnValue->set_v_bool($request['attribute_columns'][$i]['value']['v_bool']);
                        break;  
                    case ColumnType::DOUBLE:
                        $columnValue->set_v_double($request['attribute_columns'][$i]['value']['v_double']);
                        break;
                    case ColumnType::BINARY:
                        $columnValue->set_v_binary($request['attribute_columns'][$i]['value']['v_binary']);
                        break;
                    default:
                      $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                }
                $attributeColumn->set_value($columnValue);
                $pbMessage->set_attribute_columns($i, $attributeColumn);
            }
        }
         
        $pbMessage->set_table_name($request['table_name']);
        $pbMessage->set_condition($condition);
         
        return $pbMessage->SerializeToString();
    }

    private function encodeUpdateRowRequest($request)
    {
        $pbMessage = new UpdateRowRequest();
        $pbMessage->set_table_name($request["table_name"]);
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
        $pbMessage->set_condition($condition);
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['attribute_columns']))
        {
            for ($i=0; $i < count($request['attribute_columns']); $i++)
            {
                $attributeColumn = new ColumnUpdate();
                $columnValue = new ColumnValue();
                $attributeColumn->set_name($request['attribute_columns'][$i]['name']);
                $attributeColumn->set_type($request['attribute_columns'][$i]['type']);
                if ($request['attribute_columns'][$i]['type'] == OperationType::DELETE)
                {
                    $pbMessage->set_attribute_columns($i, $attributeColumn);
                    continue;
                }
                 
                $columnValue->set_type($request['attribute_columns'][$i]['value']['type']);
                switch ($request['attribute_columns'][$i]['value']['type'])
                {
                    case ColumnType::INTEGER:
                        $columnValue->set_v_int($request['attribute_columns'][$i]['value']['v_int']);
                        break;  
                    case ColumnType::STRING:
                        $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                        break;
                    case ColumnType::BOOLEAN:
                        $columnValue->set_v_bool($request['attribute_columns'][$i]['value']['v_bool']);
                        break;  
                    case ColumnType::DOUBLE:
                        $columnValue->set_v_double($request['attribute_columns'][$i]['value']['v_double']);
                        break;
                    case ColumnType::BINARY:
                        $columnValue->set_v_binary($request['attribute_columns'][$i]['value']['v_binary']);
                        break;
                    default:
                      $columnValue->set_v_string($request['attribute_columns'][$i]['value']['v_string']);
                }
                $attributeColumn->set_value($columnValue);
                $pbMessage->set_attribute_columns($i, $attributeColumn);
            }
        }
         
        return $pbMessage->SerializeToString();
    }

    private function encodeDeleteRowRequest($request)
    {
        $pbMessage = new DeleteRowRequest();
        $pbMessage->set_table_name($request["table_name"]);
        $condition = new Condition();
        $condition->set_row_existence($request['condition']['row_existence']);
        $pbMessage->set_condition($condition);
         
        for ($i=0; $i < count($request['primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['primary_key'][$i]['name']);
            $columnValue->set_type($request['primary_key'][$i]['value']['type']);
            switch ($request['primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    $columnValue->set_v_string($request['primary_key'][$i]['value']['v_string']);
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_primary_key($i, $pkColumn);
        }
        return $pbMessage->SerializeToString();
    }

    private function encodeBatchGetRowRequest($request)
    {
        $pbMessage = new BatchGetRowRequest();
 
        if(!empty($request['tables'])){
            for ($m = 0; $m < count($request['tables']); $m++) {
                $tableInBatchGetRowRequest = new TableInBatchGetRowRequest();
                $tableInBatchGetRowRequest->set_table_name($request['tables'][$m]['table_name']);
                if(!empty($request['tables'][$m]['rows'])){
                    for ($n = 0; $n < count($request['tables'][$m]['rows']); $n++) {
                        $rowInBatchGetRowRequest = new RowInBatchGetRowRequest();
                        for ($i = 0; $i < count($request['tables'][$m]['rows'][$n]['primary_key']); $i++) {
                            $pkColumn = new Column();
                            $columnValue = new ColumnValue();
                            $pkColumn->set_name($request['tables'][$m]['rows'][$n]['primary_key'][$i]['name']);
                            $columnValue->set_type($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['type']);
                            switch ($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $columnValue->set_v_int($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $columnValue->set_v_string($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $columnValue->set_v_bool($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $columnValue->set_v_double($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $columnValue->set_v_binary($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_binary']);
                                    break;
                                default:
                                    $columnValue->set_v_string($request['tables'][$m]['rows'][$n]['primary_key'][$i]['value']['v_string']);
                            }
                            $pkColumn->set_value($columnValue);
                            $rowInBatchGetRowRequest->set_primary_key($i, $pkColumn);
                        }
                        $tableInBatchGetRowRequest->set_rows($n, $rowInBatchGetRowRequest);
                    }
                }
 
                if (!empty($request['tables'][$m]['columns_to_get'])) {
                    for ($c = 0; $c < count($request['tables'][$m]['columns_to_get']); $c++) {
                        $tableInBatchGetRowRequest->set_columns_to_get($c, $request['tables'][$m]['columns_to_get'][$c]);
                    }
                }
                $pbMessage->set_tables($m, $tableInBatchGetRowRequest);
            }
        }
        return $pbMessage->SerializeToString();
    }

    private function encodeBatchWriteRowRequest($request)
    {

        $pbMessage = new BatchWriteRowRequest();

        for ($m = 0; $m < count($request['tables']); $m++) {
            $tableInBatchGetWriteRequest = new TableInBatchWriteRowRequest();
            $tableInBatchGetWriteRequest->set_table_name($request['tables'][$m]['table_name']);
            if (!empty($request['tables'][$m]['put_rows'])) {
                for ($p = 0; $p < count($request['tables'][$m]['put_rows']); $p++) {
                    $putRowItem = new PutRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['put_rows'][$p]['condition']['row_existence']);
                    $putRowItem->set_condition($condition);
 
                    for ($n = 0; $n < count($request['tables'][$m]['put_rows'][$p]['primary_key']); $n++) {
                        $pkColumn = new Column();
                        $columnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['name']);
                        $columnValue->set_type($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['type']);
                        switch ($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['type']) {
                            case ColumnType::INTEGER:
                                $columnValue->set_v_int($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $columnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $columnValue->set_v_bool($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $columnValue->set_v_double($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $columnValue->set_v_binary($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_binary']);
                                break;
                            default:
                                $columnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['primary_key'][$n]['value']['v_string']);
                        }
                        $pkColumn->set_value($columnValue);
                        $putRowItem->set_primary_key($n, $pkColumn);
                    }
                    if (!empty($request['tables'][$m]['put_rows'][$p]['attribute_columns'])) {
                        for ($c = 0; $c < count($request['tables'][$m]['put_rows'][$p]['attribute_columns']); $c++) {
                            $putRowAttributeColumn = new Column();
                            $putRowColumnValue = new ColumnValue();
                            $putRowAttributeColumn->set_name($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['name']);
                            $putRowColumnValue->set_type($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['type']);
                            switch ($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $putRowColumnValue->set_v_int($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $putRowColumnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $putRowColumnValue->set_v_bool($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $putRowColumnValue->set_v_double($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $putRowColumnValue->set_v_binary($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_binary']);
                                    break;
                                default:
                                    $putRowColumnValue->set_v_string($request['tables'][$m]['put_rows'][$p]['attribute_columns'][$c]['value']['v_string']);
                            }
                            $putRowAttributeColumn->set_value($putRowColumnValue);
                            $putRowItem->set_attribute_columns($c, $putRowAttributeColumn);
                        }
                    }
                    $tableInBatchGetWriteRequest->set_put_rows($p, $putRowItem);
                }
            }
 
            if (!empty($request['tables'][$m]['update_rows'])) {
                for ($j = 0; $j < count($request['tables'][$m]['update_rows']); $j++) {
                    $updateRowItem = new UpdateRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['update_rows'][$j]['condition']['row_existence']);
                    $updateRowItem->set_condition($condition);
                    for ($b = 0; $b < count($request['tables'][$m]['update_rows'][$j]['primary_key']); $b++) {
                        $pkColumn = new Column();
                        $updateRowColumnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['name']);
                        $updateRowColumnValue->set_type($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['type']);
                        switch ($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['type']) {
                            case ColumnType::INTEGER:
                                $updateRowColumnValue->set_v_int($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $updateRowColumnValue->set_v_bool($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $updateRowColumnValue->set_v_double($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $updateRowColumnValue->set_v_binary($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_binary']);
                                break;
                            default:
                                $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['primary_key'][$b]['value']['v_string']);
                        }
                        $pkColumn->set_value($updateRowColumnValue);
                        $updateRowItem->set_primary_key($b, $pkColumn);
                    }
 
                    if (!empty($request['tables'][$m]['update_rows'][$j]['attribute_columns'])) {
                        for ($i = 0; $i < count($request['tables'][$m]['update_rows'][$j]['attribute_columns']); $i++) {
                            $updateRowAttributeColumn = new ColumnUpdate();
                            $updateRowColumnValue = new ColumnValue();
                            $updateRowAttributeColumn->set_name($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['name']);
                            $updateRowAttributeColumn->set_type($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['type']);
                            if ($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['type'] == OperationType::DELETE) {
                                $updateRowItem->set_attribute_columns($i, $updateRowAttributeColumn);
                                continue;
                            }
 
                            $updateRowColumnValue->set_type($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['type']);
                            switch ($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['type']) {
                                case ColumnType::INTEGER:
                                    $updateRowColumnValue->set_v_int($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_int']);
                                    break;
                                case ColumnType::STRING:
                                    $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_string']);
                                    break;
                                case ColumnType::BOOLEAN:
                                    $updateRowColumnValue->set_v_bool($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_bool']);
                                    break;
                                case ColumnType::DOUBLE:
                                    $updateRowColumnValue->set_v_double($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_double']);
                                    break;
                                case ColumnType::BINARY:
                                    $updateRowColumnValue->set_v_binary($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_binary']);
                                    break;
                                default:
                                    $updateRowColumnValue->set_v_string($request['tables'][$m]['update_rows'][$j]['attribute_columns'][$i]['value']['v_string']);
                            }
                            $updateRowAttributeColumn->set_value($updateRowColumnValue);
                            $updateRowItem->set_attribute_columns($i, $updateRowAttributeColumn);
                        }
                    }
                    $tableInBatchGetWriteRequest->set_update_rows($j, $updateRowItem);
                }
            }
 
            if (!empty($request['tables'][$m]['delete_rows'])) {
                for ($k = 0; $k < count($request['tables'][$m]['delete_rows']); $k++) {
                    $deleteRowItem = new DeleteRowInBatchWriteRowRequest();
                    $condition = new Condition();
                    $condition->set_row_existence($request['tables'][$m]['delete_rows'][$k]['condition']['row_existence']);
                    $deleteRowItem->set_condition($condition);
                    for ($a = 0; $a < count($request['tables'][$m]['delete_rows'][$k]['primary_key']); $a++) {
                        $pkColumn = new Column();
                        $deleteRowColumnValue = new ColumnValue();
                        $pkColumn->set_name($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['name']);
                        $deleteRowColumnValue->set_type($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['type']);
                        switch ($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['type']) {
                            case ColumnType::INTEGER:
                                $deleteRowColumnValue->set_v_int($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_int']);
                                break;
                            case ColumnType::STRING:
                                $deleteRowColumnValue->set_v_string($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_string']);
                                break;
                            case ColumnType::BOOLEAN:
                                $deleteRowColumnValue->set_v_bool($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_bool']);
                                break;
                            case ColumnType::DOUBLE:
                                $deleteRowColumnValue->set_v_double($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_double']);
                                break;
                            case ColumnType::BINARY:
                                $deleteRowColumnValue->set_v_binary($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_binary']);
                                break;
                            default:
                                $deleteRowColumnValue->set_v_string($request['tables'][$m]['delete_rows'][$k]['primary_key'][$a]['value']['v_string']);
                        }
                        $pkColumn->set_value($deleteRowColumnValue);
                        $deleteRowItem->set_primary_key($a, $pkColumn);
                    }
                    $tableInBatchGetWriteRequest->set_delete_rows($k, $deleteRowItem);
                }
            }
            //整体设置
            $pbMessage->set_tables($m, $tableInBatchGetWriteRequest);
        }
        return $pbMessage->SerializeToString();

    }

    private function encodeGetRangeRequest($request)
    {

        $pbMessage = new GetRangeRequest();
        for ($i=0; $i < count($request['inclusive_start_primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['inclusive_start_primary_key'][$i]['name']);
            $columnValue->set_type($request['inclusive_start_primary_key'][$i]['value']['type']);
            switch ($request['inclusive_start_primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['inclusive_start_primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['inclusive_start_primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['inclusive_start_primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['inclusive_start_primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['inclusive_start_primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    if(!empty($request['inclusive_start_primary_key'][$i]['value']['v_string'])){
                            $columnValue->set_v_string($request['inclusive_start_primary_key'][$i]['value']['v_string']);
                    }
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_inclusive_start_primary_key($i, $pkColumn);
        }
        for ($i=0; $i < count($request['exclusive_end_primary_key']); $i++)
        {
            $pkColumn = new Column();
            $columnValue = new ColumnValue();
            $pkColumn->set_name($request['exclusive_end_primary_key'][$i]['name']);
            $columnValue->set_type($request['exclusive_end_primary_key'][$i]['value']['type']);
            switch ($request['exclusive_end_primary_key'][$i]['value']['type'])
            {
                case ColumnType::INTEGER:
                    $columnValue->set_v_int($request['exclusive_end_primary_key'][$i]['value']['v_int']);
                    break;  
                case ColumnType::STRING:
                    $columnValue->set_v_string($request['exclusive_end_primary_key'][$i]['value']['v_string']);
                    break;
                case ColumnType::BOOLEAN:
                    $columnValue->set_v_bool($request['exclusive_end_primary_key'][$i]['value']['v_bool']);
                    break;  
                case ColumnType::DOUBLE:
                    $columnValue->set_v_double($request['exclusive_end_primary_key'][$i]['value']['v_double']);
                    break;
                case ColumnType::BINARY:
                    $columnValue->set_v_binary($request['exclusive_end_primary_key'][$i]['value']['v_binary']);
                    break;
                default:
                    if(!empty($request['exclusive_end_primary_key'][$i]['value']['v_string'])){
                        $columnValue->set_v_string($request['exclusive_end_primary_key'][$i]['value']['v_string']);
                    }
            }
            $pkColumn->set_value($columnValue);
            $pbMessage->set_exclusive_end_primary_key($i, $pkColumn);
        }
         
        if (!empty($request['columns_to_get']))
        {
            for ($i = 0; $i < count($request['columns_to_get']); $i++)
            {
                $pbMessage->set_columns_to_get($i, $request['columns_to_get'][$i]);
            }
        }
         
        $pbMessage->set_table_name($request['table_name']);

        if (isset($request['limit'])) {
            $pbMessage->set_limit($request['limit']);
        }
        $pbMessage->set_direction($request['direction']);
        return $pbMessage->SerializeToString();

    }

    public function handleBefore($context)
    {
        $request = $context->request;
        $apiName = $context->apiName;

        $debugLogger = $context->clientConfig->debugLogHandler;
        if ($debugLogger != null) {
            $debugLogger("$apiName Request " . json_encode($request));
        }

        $this->checkParameter($apiName, $request);

        // preprocess the request if neccessary 
        $preprocessMethod = "preprocess" . $apiName . "Request";
        if (method_exists($this, $preprocessMethod)) {
            $request = $this->$preprocessMethod($request);
        }

        $encodeMethodName = "encode" . $apiName . "Request";
        $context->requestBody = $this->$encodeMethodName($request);
    }

    public function handleAfter($context)
    {
        if ($context->otsServerException != null) {
            return;
        }
    }
}
