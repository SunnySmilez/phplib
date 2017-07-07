<?php
namespace S\Log\Formatter;

class Error extends Abstraction {

    public function format(array $message) {
        if (isset($message['exception'])) {
            /**
             * @var \Exception $e
             */
            $e = $message['exception'];
            if ($e instanceof \Base\Exception\Controller) {
                $message['error_type'] = "controller.exception";
            } else if ($e instanceof \Base\Exception\Dao) {
                $message['error_type'] = "dao.exception";
            } else if ($e instanceof \Base\Exception\Data) {
                $message['error_type'] = "data.exception";
            } else if ($e instanceof \Base\Exception\Service) {
                $message['error_type'] = "service.exception";
            } else if ($e instanceof \Error){
                $message['error_type'] = "error";
            } else {
                $message['error_type'] = "exception";
            }
            $message['error_code']    = $e ? $e->getCode() : null;
            $message['error_message'] = $e ? $e->getMessage() : null;
            $message['error_trace']   = $e ? \S\Util\Exception::getFullTraceAsString($e) : null;
            unset($message['exception']);
        }

        $message = array_merge($this->getCommon(), $message);

        return $message;
    }

}