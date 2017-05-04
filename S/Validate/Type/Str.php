<?php
namespace S\Validate\Type;

class Str extends \S\Validate\Abstraction {
	protected $_default_settings = array(
		'min'   => 1,
		'max'   => 255,
	);

	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		$len = strlen($value);
		$min = $option['min'];
		$max = $option['max'];
		if ($len < $min || $len > $max) {
			$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'Type'=>'error.string_error');
			return $this->exception_check_params_handle($filter, $error, $option['defalut']);
		}

		return $value;
	}
}