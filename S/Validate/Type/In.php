<?php
namespace S\Validate\Type;

/**
 * @description
 * 参数检查类：检验参数是否在指定范围内
 *
 * <code>
 *  'in_array' => array('value'=>'aaa', 'option'=>array('haystack'=>array('a', 'b'), 'error'=>'validate.in_array_error')),
 *
 *  'in_string' => array('value'=>'dede', 'option'=>array('haystack'=>'xcyvubino;m', 'error'=>'validate.in_string_error')),
 * </code>
 *
 */
class In extends \S\Validate\Abstraction {
	protected $_default_settings = array(
		'default'   => false,
		'haystack'  => array(),
	);

	protected function action($value, $filter = \S\Validate\Handler::VALIDATOR, array $option = array()) {
		if (is_array($option['haystack'])) {
			if (in_array($value, $option['haystack'], $option['strict']??false)) {
				return $value;
			}
		} elseif (is_string($option['haystack'])) {
			if (strpos($option['haystack'], $value) !== false) {
				return $value;
			}
		}

		$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'error.in_not_in_haystack');
		return $this->exception_check_params_handle($filter, $error, $option['defalut']);
	}

	protected function is_support_filter() {
		return true;
	}

}