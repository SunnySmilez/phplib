<?php
namespace S\Validate\Type;

/**
 * 只支持http和https验证
 */
class Url extends \S\Validate\Abstraction {
	protected $_default_settings = array(
		'filter_xss'        => true,
		'is_trusted'        => false,   // 是否为可信的回跳地址
		'trusted_domains'   => array(), // 自定义的一些可信域
	);

	protected function action($value, $filter=\S\Validate\Handler::VALIDATOR, array $option=array()) {
		$error = array('error_message'=>$option['error_message'], 'error'=> $option['error'], 'Type'=>'error.url_format');

		$parse = parse_url($value);

		// 这里比原来的严格，不再支持相对路径
		if (!$parse || !isset($parse['host']) || !in_array(strtolower($parse['scheme']), array('http', 'https'))) {
			return $this->exception_check_params_handle($filter, $error);
		}

		if ($option['filter_xss']) {
			$value = $this->filter_xss($value);
		}

		if ($option['is_trusted']) {
			if (!$this->is_trusted_domain($parse['host'], $option['trusted_domains'])) {
				$error['Type']  = 'error.url_not_trusted';
				return $this->exception_check_params_handle($filter, $error);
			}
		}

		return $value;
	}

	public function filter_xss($url) {
		$illegal_chars  = array('\'','"',';','<','>','(',')','{','}','[',']');
		$encoded_chars  = array('%27','%22','%3B','%3C','%3E','%28','%29','%7B','%7D','%5B','%5D');
		return str_replace($illegal_chars, $encoded_chars, $url);
	}

	/**
	 * 可信的域
	 *
	 * @param $host
	 * @param array $other_trusted_domains
	 * @return bool
	 */
	public function is_trusted_domain($host, $other_trusted_domains=array()) {
		$domains = array_merge(\S\Config::confServer('app.trusted_domains'), $other_trusted_domains);
		foreach ($domains as $domain){
			if ($this->is_subdomain($host, $domain)) {
				return true;
			}
		}
		return false;
	}

	public function is_subdomain($host, $domain) {
		return preg_match("/(\.|^){$domain}$/", $host);
	}

}