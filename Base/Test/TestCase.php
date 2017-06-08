<?php
namespace Base\Test;

class TestCase extends \PHPUnit\Framework\TestCase {
    /**
     * @var \Yaf\Application
     */
    protected static $_app;

    public static function setUpBeforeClass(){
        $application = \Yaf\Registry::get('application');
        if (!$application) {
            self::$_app = (new \Yaf\Application(APP_CONF . '/application.ini'))->bootstrap();
            \Yaf\Registry::set('application', self::$_app);
        }else{
            self::$_app = $application;
        }
    }

    protected function execRequest($method, $controller, $params = array()){
        $request = new \Yaf\Request\Simple($method, "index", $controller, "index", $params);
        $_REQUEST = $request->getParams();
        if(strtolower($method) === "post"){
            $_POST = $request->getParams();
        }else{
            $_GET = $request->getParams();
        }
        self::$_app->getDispatcher()->dispatch($request);
        $result = \Yaf\Registry::get('test_response');
        \Yaf\Registry::del('test_response');
        return $result;
    }
}