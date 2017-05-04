<?php
namespace S\Validate\Type;

/**
 * <code>
 *  'int' => array(value=>21212121, 'rule'=>'digit', 'option'=>array('digit_type'=>'int_32', 'error'=>''validate.demo'')),
 *
 *  'int' => array(value=>21212121, 'rule'=>'digit', 'option'=>array('digit_type'=>'int_32', 'error_message'=>'xxxxx')),
 *
 *  'int' => array(value=>21212121, 'rule'=>'digit', 'filter'=>Validator::FILTER, 'option'=>array('default'=>1)),
 * </code>
 */
class Digit extends \S\Validate\Abstraction {
	private $_is_extract_array = '';

	private $_default_digit_type = array(
		'int_32'            => array('min'=>-2147483648, 'max'=>2147483647),
		'unsigned_int_32'   => array('min'=>0, 'max'=>4294967295),
		'byte'              => array('min'=>-128, 'max'=>127),
		'unsigned_byte'     => array('min'=>0, 'max'=>255),
		'short'             => array('min'=>-32768, 'max'=>32767),
		'unsigned_short'    => array('min'=>0, 'max'=>65535),
		'int_64'            => array('min'=>-9223372036854775808, 'max'=>9223372036854775807),
		'unsigned_int_64'   => array('min'=>0, 'max'=>18446744073709551615),
	);

	protected $_default_settings = array(
		'min'           => false,
		'max'           => false,
		'digit_type'    => false,
		'default'       => false,
		'is_extract_array'  => false,
	);

	protected function action($value, $filter = \S\Validate\Handler::VALIDATOR, array $option = array()) {
		$this->_is_extract_array = (bool)$option['is_extract_array'];

		if ($option['digit_type']) {
			if (!isset($this->_default_digit_type[$option['digit_type']])) {
				throw new \S\Exception('digit_type '. $option['digit_type'] .' does not support');
			}
			$option = array_merge($this->_default_digit_type[$option['digit_type']], $option);
		}

		if (is_array($value)) {
			$ret = array();
			foreach ($value as $k=>$v) {
				$ret[$k] = $this->_check_digit($v, $filter, $option);
			}
			return $ret;
		}

		return $this->_check_digit($value, $filter, $option);
	}

	private function _check_digit($value, $filter, $option) {
		$error = false;
		if (is_numeric($value) && is_int(abs($value)) || ctype_digit($value)) {
			if ($option['min'] !== false && $value < $option['min']) {
				$error = 'error.digit_too_small';
			} elseif ($option['max'] !== false && $value > $option['max']) {
				$error = 'error.digit_too_large';
			}
		} else {
			$error = 'error.digit_not_digit';
		}

		if ($error) {
			$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], $error);
			return $this->exception_check_params_handle($filter, $error, $option['defalut']);
		}

		return $value;
	}

	public function is_extract_array() {
		return $this->_is_extract_array;
	}

	protected function is_support_filter() {
		return true;
	}
}