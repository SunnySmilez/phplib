<?php
namespace S\Validate\Type;

class Phone extends \S\Validate\Abstraction {
	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		if (!preg_match('/^1[345789]\d{9}$/', $value)) {
			$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.phone_format_error');
			return $this->exception_check_params_handle($filter, $error);
		}
		return $value;
	}
}