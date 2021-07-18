<?php

namespace Smbear\Payeezy\Traits;

use Smbear\Payeezy\Exceptions\ConfigException;

trait PayeezyConfig
{
    /**
     * @var array 模型下的配置文件
     */
    public $config;

    /**
     * @var string 是生产模型还是沙盒模型
     */
    public $environment = 'sandbox';

    /**
     * @Notes:设置 environment
     *
     * @param string $environment
     * @Author: smile
     * @Date: 2021/6/8
     * @Time: 18:53
     */
    public function setEnvironment(string $environment = '')
    {
        $this->environment = $environment ?: config('payeezy.environment');
    }

    /**
     * @Notes:设置config，并判断config参数是否正常
     *
     * @param array $dependencies
     * @throws ConfigException
     * @Author: smile
     * @Date: 2021/6/8
     * @Time: 18:53
     */
    public function getConfig(array $dependencies)
    {
        if (is_null($this->config)){
            $environment = $this->environment;

            array_map(function ($item) use ($environment) {
                if (empty(config('payeezy.'.$environment.'.'.$item))){
                    throw new ConfigException('config '.$environment .'.'.$item.' 参数为空');
                }
            }, $dependencies);

            $this->config = config('payeezy.'.$environment);
        }
    }
}