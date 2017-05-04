<?php
namespace S\Validate\Type;

/**
 * 验证日期格式为：2014-01-02这种格式
 * Class Date
 * @package Validator
 */
class Date extends \S\Validate\Abstraction {
	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		$value = trim($value);
		if (!$value || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
			$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.fatal_date_error');
			return $this->exception_check_params_handle($filter, $error);
		}
		return $value;
	}
}