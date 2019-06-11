<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/10
 * Time: 上午10:10
 */

namespace SwoftRewrite\Bean\Definition;


class ArgsInjection
{
    private $value;
    private $isRef = false;

    public function __construct($value,bool $isRef)
    {
        $this->isRef = $isRef;
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isRef()
    {
        return $this->isRef;
    }
}