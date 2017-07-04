<?php
namespace S\Captcha;

use S\Exception;

/**
 * 提供验证码服务
 *
 * 验证码有效时间默认10分钟
 *
 * <demo>
 * 发送短信验证码  使用openapi的短信服务来发送短信(注意：使用openapi的短信服务需要先在openapi平台上开通短信服务)
 * $captcha = new \S\Captcha\Captcha('id', 'digit', 6); //6位数字验证码
 * try{
 *      $ret = $captcha->show('sms', array(
 *                                  'service' => 'openapi',
 *                                  'phone' => '12345678910',
 *                                  'template' => "验证码为%captcha%, 有效时间十分钟",
 *                                  'mode' => 'test' //openapi服务的短信发送模式 不赋值则使用默认发送模式(其他服务不需要此值)
 *                                  )
 *      );
 * } catch (\S\Exception $e){
 *      //异常处理
 * }
 * template中得%captcha%的位置将会被替换成验证码
 *
 *
 * 获取图片验证码
 * $captcha = new \S\Captcha\Captcha();
 * $id = $captcha->create('mix', 4, 'id'); //4位的数字字母混合验证码
 * $captcha->show('image', array('width' => 80, 'height' => 30));
 * show方法将自动生成验证码图片并输出
 *
 * 验证
 * $ret = \S\Captcha\Captcha::validate('123456', 'id');
 * 验证时不区分字母的大小写
 * </demo>
 *
 */
class Captcha {

    const TYPE_DIGIT        = 'digit';   //数字验证码
    const TYPE_ENGLISH      = 'english'; //英文类型验证码 大小写均有(验证时不限大小写)
    const TYPE_ENGLISH_LOW  = 'english_low';  //小写英文验证码(验证时不限大小写)
    const TYPE_ENGLISH_HIGH = 'english_high'; //大写英文验证码(验证时不限大小写)
    const TYPE_MIX          = 'mix'; //混合类型验证码 包括数字 大写字母 小写字母(验证时不限大小写)

    const MODE_SMS  = 'sms';
    const MODE_PIC  = 'pic';
    const MODE_MAIL = 'mail';

    public $code;

    /**
     * 生成验证码并存储
     *
     * @param string $type   验证码类型 支持类型见上
     * @param int    $length 验证码的长度
     * @param string $id     验证码id
     * @param int    $ttl    验证码有效时间 默认600秒
     * @throws \S\Exception $type的类型不存在
     */
    public function __construct($id, $type, $length, $ttl = 600) {
        $this->code = Util::create($type, $length);
        Store::set($id, $this->code, $ttl);

        if (\Core\Env::isPhpUnit()) {
            \Yaf\Registry::set('captcha', $this->code);
        }
    }

    /**
     * 展示验证码
     *
     * @param string $mode 展示模式 当前支持'sms'(短信), 'image'(图片)
     * @param array  $args 参数数组
     *                     短信: array(
     *                     'phone'    手机号
     *                     'template' 正文模板 用%captcha%代替验证码的位置
     *                     'service'  使用的短信服务 不配置则默认使用openapi的短信服务
     *                     )
     *                     图片: array(
     *                     'width' 图片宽度 int 默认80
     *                     'height'图片高度 int 默认30
     *                     'size'  验证码文字点字体大小 int 不设置则使用默认值
     *                     )
     * @return mixed
     * @throws Exception
     */
    public function show($mode, array $args) {
        //todo 添加频率限制器
        if (!in_array($mode, array(self::MODE_SMS, self::MODE_PIC, self::MODE_MAIL))) {
            throw new Exception("captcha show mode: `{$mode}` is not support");
        }

        if(!\Core\Env::isPhpUnit()){
            //todo 支持自定义handler
            $handler = __NAMESPACE__."\\Handler\\".ucfirst($mode);
            $args['code'] = $this->code;
            $ret = call_user_func(array(new $handler, __FUNCTION__), $args);
            //todo 判断发送结果 失败抛出异常

            return $ret;
        }else{
            return true;
        }
    }

    /**
     * 校验验证码
     * todo 支持多种校验方式 验证码不覆盖的需求
     *
     * @param string $val_code 用户输入的验证码
     * @param string $id       验证码ID
     * @param int    $clear    验证码的失效规则 在校验int次后失效
     * @return bool|null       成功返回true 失败时返回false 验证超过限定次数时返回null
     */
    public static function validate($val_code, $id, $clear = 3) {
        if (empty($val_code)) {
            return false;
        }

        $freq = new \S\Security\Freq();
        $rule_name = 'capital_code_val_limit';

        $code = Store::get($id);
        if (strtolower($code) === strtolower($val_code)) {
            //校验成功
            $ret = true;
            Store::clear($id);
            $freq->clear($rule_name, $id);
        } else {
            //校验失败
            $ret = false;
            //失败频次限制
            $check = $freq->add($rule_name, $id, $clear - 1, 600);
            if (!$check) { //超过允许失败次数
                Store::clear($id);
                $freq->clear($rule_name, $id);
                $ret = null;
            }
        }

        return $ret;
    }

}