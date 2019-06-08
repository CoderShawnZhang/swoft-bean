<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/6
 * Time: 下午2:45
 */

namespace SwoftRewrite\Bean;


use SwfotRewrite\Bean\Container;

class BeanFactory
{
    public static function addDefinitions(array $definitions)
    {
        Container::getInstance()->addDefinitions($definitions);
    }

    public static function addAnnotations(array $annotations)
    {
        Container::getInstance()->addAnnotations($annotations);
    }

    public static function addParsers(array $parsers)
    {
        Container::getInstance()->addParsers($parsers);
    }
}