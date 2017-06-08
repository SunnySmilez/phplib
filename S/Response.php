<?php
namespace S;

class Response {
    const FORMAT_JSON   = 'json';
    const FORMAT_PLAIN  = 'plain';
    const FORMAT_HTML   = 'html';

    const RET_CODE = '2000000';
    const RET_MSG = 'SUCC';

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
     * json输出模式
     * @param array $data
     * @param string $retcode
     * @param string $msg
     * @return bool
     */
    public static function displayJson(array $data, $retcode='', $msg=''){
        @header('Content-type: application/json');
        echo json_encode(
            array(
                'retcode' => $retcode?:self::RET_CODE,
                'msg'     => $msg?:self::RET_MSG,
                'data'    => $data
            )
        );
        return true;
    }

    /**
     * 直接输出内容
     *
     * @param $data
     * @return bool
     */
    public static function displayPlain($data){
        @header('Content-type: text/plain');
        echo $data;
        return true;
    }

    /**
     * 渲染视图
     *
     * @param \Yaf\View_Interface $view
     * @param $data
     * @return bool
     */
    public static function displayView(\Yaf\View_Interface $view, $data){
        $request = \Yaf\Application::app()->getDispatcher()->getRequest();
        $tpl_path = APP_VIEW ."/". str_replace('_', '/', $request->controller).'.phtml';
        $view->display($tpl_path, $data);
        return true;
    }

	public static function cacheHeader($expires) {
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