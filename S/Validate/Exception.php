<?php
namespace S\Validate;

class Exception extends \Exception {
	public function __construct($message, $code=0, \Exception $previous=null) {
        $file = dirname(__FILE__)."/Conf/Error.php";
		if (is_array($message)) {
			if (isset($message['Type'])) {
				$type = $message['Type'];
				unset($message['Type']);
			} else {
				$type = $message[0];
				unset($message[0]);
			}

            $config_file = \S\Config::file($file);
			list($code, $msg) = $config_file[$type];

			if (isset($message['error_message']) && $message['error_message']) {
				$msg = $message['error_message'];
				unset($message['error_message']);
			}
			$count      = count($message);
			$count_match= preg_match_all('/(?<!%)%[\.a-zA-Z0-9]+/', $msg, $m);
			if ($count_match > $count) {
				$message = array_merge($message, array_fill(0, $count_match-$count, ''));
			}
			$message = vsprintf($msg, $message);
		} elseif (substr($message, 0, 6) === 'error.') {
            $config_file = \S\Config::file($file);
			list($code, $message) = $config_file[(substr($message, 6))];
		} elseif (substr($message, 0, 9) === 'validate.'){
            list($code, $message) = array_values(\S\Config::confError($message));
        }

		parent::__construct($message, (int)$code, $previous);
	}
}
