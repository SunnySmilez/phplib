<?php
namespace S\Validate\Type;

class Email extends \S\Validate\Abstraction {
	protected $_default_settings = array(
		'min'   => 6,
		'max'   => 64,
		'mx'    => false,
	);

	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		$value  = trim($value);
		$length = strlen($value);

		$error = array('error_message'=>$option['error_message'], 'error'=> $option['error']);
		if ($length < $option['min'] || $length > $option['max']) {
			$error['Type']  = 'error.email_length';
			return $this->exception_check_params_handle($filter, $error);
		}

		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$error['Type']  = 'error.email_length';
			return $this->exception_check_params_handle($filter, $error);
		}

		if ($option['mx']) {
			if (!$this->_mx_exist(substr($value, strpos($value, '@')+1))) {
				$error['Type']  = 'error.email_mx_not_exist';
				return $this->exception_check_params_handle($filter, $error);
			}
		}

		return $value;
	}

	/**
	 * @param $host
	 * @return bool
	 */
	private function _mx_exist($host) {
		return getmxrr($host, $arr);
	}
}