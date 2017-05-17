<?php
namespace S\Log\Formatter;

use \S\Util\Exception as ExceptionUtil;

class Warning extends Abstraction {

    public function format(array $message) {
        if (isset($message['exception'])) {
            /** @var \Exception $e */
            $e = $message['exception'];
            if ($e instanceof \Base\Exception\Controller) {
                $message['error_type'] = "controller.exception";
            } elseif ($e instanceof \Base\Exception\Dao) {
                $message['error_type'] = "dao.exception";
            } elseif ($e instanceof \Base\Exception\Data) {
                $message['error_type'] = "data.exception";
            } elseif ($e instanceof \Base\Exception\Service) {
                $message['error_type'] = "service.exception";
            } else {
                $message['error_type'] = "exception";
            }
            $message['error_code']    = $e ? $e->getCode() : null;
            $message['error_message'] = $e ? $e->getMessage() : null;
            $message['error_trace']   = $e ? ExceptionUtil::getFullTraceAsString($e) : null;
            unset($message['exception']);
        }

        $message = array_merge($this->getCommon(), $message);

        return json_encode($message, JSON_UNESCAPED_UNICODE);
    }
    
}