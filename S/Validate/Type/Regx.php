<?php
namespace S\Validate\Type;

/**
 * config/regx.php中会定义很多常用的正则匹配规则
 *
 * <code>
 *
 * </code>
 */
class Regx extends \S\Validate\Abstraction {
	protected $_default_settings = array(
		'regx'  => false,
	);

	protected function action($value, $filter = \S\Validate\Handler::VALIDATOR, array $option = array()) {
		if (!isset($option['regx'])) {
			throw new \S\Exception('option[regx] is not setted');
		}
		if (!preg_match($option['regx'], $value)) {
			$this->throw_exception(array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'Type'=>'error.regx_not_matched'));
		}
        return $value;
	}
}