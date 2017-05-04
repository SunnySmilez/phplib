<?php
namespace S;

class Response {
    const FORMAT_JSON   = 'json';
    const FORMAT_PLAIN  = 'plain';
    const FORMAT_HTML   = 'html';

	static protected $use_jsonp_var = false;
	static protected $meta = array();
    static protected $formatter = null;

    public static function setFormatter($format){
        self::$formatter = $format;
    }

    public static function getFormatter(){
        return self::$formatter?:self::FORMAT_HTML;
    }

	/**
	 * 提供json格式中元数据的定制
	 *
	 * Tutorial:
	 * <code>
	 * //默认:
	 * 	Comm_Response::out_json(0, 'okay', array('That is right'));
	 * 	//{'code' : 0, 'msg' : 'okay', 'data' : ['That is right']}
	 * //定制参数：
	 * 	Comm_Response::set_meta_data('key', 'foo');
	 * 	Comm_Response::out_json(0, 'okay', array('That is right'));
	 * 	//{'key': 'foo', 'code' : 0, 'msg' : 'okay', 'data' : ['That is right']}
	 * </code>
	 * @param string $name
	 * @param mixed $value
	 */
	public static function setMetaData($name, $value){
		self::$meta[$name] = $value;
	}

	/**
	 * 获取json结构定制的元数据
	 *
	 * @param string|null $name 数据名。可选，若空，则取null作为默认值。
	 * @return mixed 当$name为空时，返回全部meta数据，否则只返回指定的数据。若指定数据未设置过，则返回一个null
	 */
	public static function getMetaData($name = null){
		return $name ? (isset(self::$meta[$name]) ? self::$meta[$name] : null) : self::$meta;
	}

	/**
	 * 设置在输出jsonp的时候，将$callback参数作为变量处理。
	 *
	 */
	public static function useJsonpAsVar(){
		self::$use_jsonp_var = true;
	}

	/**
	 * 设置在输出jsonp的时候，将$callback参数作为变量处理
	 *
	 */
	public static function useJsonpAsCallback(){
		self::$use_jsonp_var = false;
	}

	/**
	 * 按json格式输出响应
	 *
	 * @param string|int	$code			js的错误代码/行为代码
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @param bool			$return_string	可选。是否返回一个字符串。默认情况将直接输出。
	 * @return string|void	取决与$return_string的设置。如果return_string为真，则返回渲染结果的字符串，否则直接输出，返回空
	 */
	public static function outJson($code, $message = '', $data = array(), $return_string = false) {
		$json_string = json_encode(array_merge(self::$meta, array(
			'retcode' => $code,
			'msg' => strval($message),
		), $data));
		if ($return_string) {
			return $json_string;
		} else {
			@header('Content-type: application/json');
			echo $json_string;
			return true;
		}
	}

	/**
	 * 按jsonp格式输出响应
	 *
	 * @param string		$callback		Javascript所需的回调函数名字。如果不合法，则会抛出一个异常。
	 * @param string		$code			Javascript所需的行为代码。
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @param bool			$return_string	可选。是否返回一个字符串。默认情况将直接输出。
	 * @return string|void	取决于$return_string的设置。如果return_string为真，则返回渲染结果的字符串，否则直接输出，返回空
	 *
	 * @throws Exception
	 */
	public static function outJsonp($callback, $code, $message = '', $data = array(), $return_string = false){
		if (preg_match('/^[\w\$\.]+$/iD', $callback)) {
			$jsonp = (!self::$use_jsonp_var ? "window.{$callback} && {$callback}(" : "var {$callback}=")
				. self::outJson($code, $message, $data, true) . (!self::$use_jsonp_var ? ")" : "") . ';';
			if ($return_string) {
				return $jsonp;
			} else {
				@header('Content-type: application/json');
				echo $jsonp;
				return true;
			}
		}
		throw new Exception('callback name invalid');
	}

	/**
	 * 输出需要用iframe嵌套的jsonp
	 *
	 * @param string		$callback		Javascript所需的回调函数名字。如果不合法，则会抛出一个异常。
	 * @param string		$code			Javascript所需的行为代码。
	 * @param string		$message		可选。行为所需文案或者错误详细描述。默认为空。
	 * @param mixed			$data			可选。附加数据。
	 * @see out_jsonp
	 */
	public static function outJsonpIframe($callback, $code, $message = '', $data = array()){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript">document.domain="sina.com.cn";'
			. self::outJsonp($callback, $code, $message, $data, true)
			. '</script>';
	}

	/**
	 * 直接输出内容
	 *
	 * @param string $text
	 */
	public static function outPlain($text){
		echo $text;
	}

	public function cacheHeader($expires) {
		if ($expires === false) {
			return self::header(array(
				'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
				'Cache-Control' => array(
					'no-store, no-cache, must-revalidate',
					'post-check=0, pre-check=0',
					'max-age=0'
				),
				'Pragma' => 'no-cache'
			));
		}
		$expires = is_int($expires) ? $expires : strtotime($expires);

		return self::header(array(
			'Expires' => gmdate('D, d M Y H:i:s', $expires) . ' GMT',
			'Cache-Control' => 'max-age=' . ($expires - time()),
			'Pragma' => 'cache'
		));
	}

	public static function p3p() {
		@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	}

	public static function header(array $headers, $replace=false) {
		foreach ($headers as $key=>$value) {
			@header("{$key}:{$value}", $replace);
		}
		return true;
	}

	public static function rawHttpBuildQuery(array $query) {
		return str_replace(array('~', '+'), array('%7E', '%20'), http_build_query($query));
	}

    /**
     * @param string $file 下载文件的内容 支持使用文件的绝对路径来获取
     * @param string $filename 下载文件的名称
     * @throws Exception
     */
    public static function download($file, $filename){
        ob_clean();
        ob_start();

        //清除$file中的\0 防止php_warning
        $file_cleaned = strval(str_replace("\0", "", $file));
        if(is_file($file_cleaned)){
            if(!is_readable($file_cleaned)){
                ob_end_clean();
                throw new \S\Exception("该文件不可读", 5002101);
            }
            $length = filesize($file);
            readfile($file);
        }else{
            if(!$file){
                ob_end_clean();
                throw new \S\Exception("文件内容为空", 5002102);
            }
            $length = strlen($file);
            echo $file;
        }

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        header('Content-Length: ' . $length);
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_end_flush();
    }

	public static function header404($uri='/') {
		header('HTTP/1.1 404 Not Found');
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
			.'<html><head><title>404 Not Found</title></head><body>'
			.'<h1>Not Found</h1>'
			.'<p>The requested URL '.htmlentities($uri).' was not found on this server.</p>'
			.'</body></html>';
        // 标准http协议并未规定中止请求, 此处为了防止业务代码中其他干扰性输出有添加中止逻辑
        exit;
	}
}