<?php
namespace S\Log;

/**
 * 日志记录类
 *
 * 依赖关系
 *  Log\Handler是各种日志存储方式，需要根据需求实现不同的写入方式
 *  Log\Formatter是日志的格式化方式，需要根据需求实现不同的格式化方式
 *
 * 执行流程：
 * 启动指明$channel使用的驱动存储和格式化方案
 * $channel可以有多个驱动存储方案，但只有一个格式化方案
 * 多个驱动意味着日志记录多份，发送到不同的存储
 * 一个应用可以有多个channel，对应不同的格式化方案
 *
 *
 * <code>
 * \S\Log\Logger::getInstance()->push_handler(new \S\Log\Handler\File());
 *
 * \S\Log\Logger::getInstance()->info($message, $handle_flag);
 * \S\Log\Logger::getInstance()->debug($message, $handle_flag);
 * \S\Log\Logger::getInstance()->error($message, $handle_flag);
 * </code>
 */
class Logger {

    const DEFAULT_CHANNEL = 'default';

    /**
     * @var Logger
     */
    private static $_instance = array();

    /**
     * @var Handler\Abstraction[]
     */
    private $_handlers = array();

    private $_channel = "";

    private function __construct($channel) {
        $this->_channel = $channel;
    }

    /**
     * @param string $channel
     *
     * @return Logger
     */
    public static function getInstance($channel = self::DEFAULT_CHANNEL) {
        if (self::$_instance[$channel] === null) {
            self::$_instance[$channel] = new self($channel);
        }

        return self::$_instance[$channel];
    }

    public function pushHandler(Handler\Abstraction $handler) {
        array_unshift($this->_handlers, $handler);
    }

    public function popHandler() {
        return array_shift($this->_handlers);
    }

    public function addRecord($level, array $message, $to_path) {
        if (!$this->_handlers) {
            throw new \Exception('log handler not push');
        }

        $levelName = LogLevel::$levels[$level];
        if (!$levelName) {
            throw new \Exception('level not in LogLevel');
        }

        $format = __NAMESPACE__ . "\\Formatter\\" . ucfirst($level);
        /** @var \S\Log\Formatter\Abstraction $obj_format */
        $obj_format = new $format;
        $message    = $obj_format->format($message);
        foreach ($this->_handlers as $handler) {
            /** @var \S\Log\Handler\Abstraction $handler */
            $handler->write($levelName, $message, $to_path);
        }

        return true;
    }

    public function info(array $message = array(), $to_path = "") {
        return $this->addRecord(LogLevel::INFO, $message, $to_path);
    }

    public function debug(array $message, $to_path = "") {
        return $this->addRecord(LogLevel::DEBUG, $message, $to_path);
    }

    public function error(array $message, $to_path = "") {
        return $this->addRecord(LogLevel::ERROR, $message, $to_path);
    }

    public function warning(array $message, $to_path = "") {
        return $this->addRecord(LogLevel::WARNING, $message, $to_path);
    }

    /**
     * 统计日志
     * @param array  $message
     * @param string $index       索引名称
     * @return bool
     */
    public function stat(array $message, $index) {
        $index = APP_NAME . "-" . $index;
        return $this->addRecord(LogLevel::STAT, $message, $index);
    }
}
