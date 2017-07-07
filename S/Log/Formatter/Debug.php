<?php
namespace S\Log\Formatter;

class Debug extends Abstraction {

    public function format(array $message) {
        $message = array_merge($this->getCommon(), $message);

        return $message;
    }

}