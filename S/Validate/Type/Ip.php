<?php
namespace S\Validate\Type;

class Ip extends \S\Validate\Abstraction {
	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		$ret = filter_var($value, FILTER_VALIDATE_IP);
		if ($ret === false) {
			$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.ip_format');
			return $this->exception_check_params_handle($filter, $error);
		}
		return $value;
	}
}