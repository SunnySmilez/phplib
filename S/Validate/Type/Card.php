<?php
namespace S\Validate\Type;
/**
 * 卡号验证，支持16到19位卡号
 * Class Card
 * @package S\Validate\Type
 */
class Card extends \S\Validate\Abstraction {
	protected function action($value, $filter = \S\Validate\Handler::VALIDATOR, array $option = array()) {
		$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.card_error');
		if (preg_match('/^[0-9]{9,23}$/', $value)) {
			return $value;
		} else {
			return $this->exception_check_params_handle($filter, $error);
		}
	}
}
