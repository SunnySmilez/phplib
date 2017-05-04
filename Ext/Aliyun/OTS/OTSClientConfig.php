<?php

namespace OTS;

use OTS\Retry\DefaultRetryPolicy as DefaultRetryPolicy;

/**
 * OTSClientConfig 是用来封装OTS SDK端配置的类，OTSClient对象构造时会创建OTSClientConfig对象。
 * 当你在构造OTSClient对象时传入的参数会用来构造OTSClientConfig对象。
 */
class OTSClientConfig
{
    public $endPoint;
    public $accessKeyID;
    public $accessKeySecret;
    public $instanceName;
    public $connectionTimeout = 5.0;
    public $socketTimeout = 5.0;

    /**
     * Error级别日志处理函数，默认处理函数为 defaultOTSErrorLogHandler，行为是打印到屏幕
     */
    public $errorLogHandler;
    
    /**
     * Debug级别日志处理函数，默认处理函数为 defaultOTSDebugLogHandler，行为是打印到屏幕
     */
    public $debugLogHandler;

    /**
     * @var \OTS\Retry\RetryPolicy
     * 重试策略，默认为 \OTS\Retry\DefaultRetryPolicy。
     */
    public $retryPolicy;

    /**
     * OTSClientConfig的构造函数。
     * 它的参数从 OTSClient 的构造函数中传入。具体参数说明请见 OTSClient 的构造函数。
     */
    public function __construct(array $args)
    {
        if (!isset($args['EndPoint'])) {
            throw new OTSClientException("Missing EndPoint in client config.");
        }
        
        if (!isset($args['AccessKeyID'])) {
            throw new OTSClientException("Missing AccessKeyID in client config.");
        }
        
        if (!isset($args['AccessKeySecret'])) {
            throw new OTSClientException("Missing AccessKeySecret in client config.");
        }
        
        if (!isset($args['InstanceName'])) {
            throw new OTSClientException("Missing InstanceName in client config.");
        }

        $this->endPoint = $args['EndPoint'];
        $this->accessKeyID = $args['AccessKeyID'];
        $this->accessKeySecret = $args['AccessKeySecret'];
        $this->instanceName = $args['InstanceName'];

        if (isset($args['ConnectionTimeout'])) {
            $this->connectionTimeout = $args['ConnectionTimeout'];
        }

        if (isset($args['SocketTimeout'])) {
            $this->socketTimeout = $args['SocketTimeout'];
        }

        if (!isset($args['RetryPolicy'])) {
            $this->retryPolicy = new DefaultRetryPolicy();
        } else {
            $this->retryPolicy = $args['RetryPolicy'];
        }

        $this->errorLogHandler = $args['ErrorLogHandler']?:NULL;
        $this->debugLogHandler = $args['DebugLogHandler']?:NULL;

    }

    public function getEndPoint()
    {
        return $this->endPoint;
    }

    public function getAccessKeyID()
    {
        return $this->accessKeyID;
    }

    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    public function getInstanceName()
    {
        return $this->instanceName;
    }

}

