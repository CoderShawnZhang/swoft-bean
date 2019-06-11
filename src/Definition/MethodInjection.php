<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/10
 * Time: 上午10:16
 */

namespace SwoftRewrite\Bean\Definition;


class MethodInjection
{
    private $methodName;
    private $parameters = [];

    public function __construct(string $methodName,array $parameters)
    {
        $this->methodName = $methodName;
        $this->parameters = $parameters;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }
    /**
     * @return ArgsInjection[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}