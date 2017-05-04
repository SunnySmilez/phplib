<?php
namespace S\Validate\Type;

class Money extends \S\Validate\Abstraction {
	protected function action($value, $filter = \S\Validate\Handler::VALIDATOR, array $option = array()) {
		$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.money_error');
		if (preg_match('/^[0-9]*$/', $value)) {
			return $value;
		} elseif (preg_match('/^[0-9]*\.[0-9]*$/', $value)){
            return $value;
        } else {
			return $this->exception_check_params_handle($filter, $error);
		}
	}
}