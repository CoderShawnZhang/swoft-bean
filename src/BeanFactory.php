<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/6
 * Time: 下午2:45
 */

namespace SwoftRewrite\Bean;


use SwoftRewrite\Bean\Container;
use SwoftRewrite\Console\Console;

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

    public static function init()
    {
        Container::getInstance()->init();
    }

    public static function getBean(string $name)
    {
        return Container::getInstance()->get($name);
    }

    public static function getSingleton(string $name)
    {
        return Container::getInstance()->getSingleton($name);
    }

    public static function isSingleton(string $name): bool
    {
        return Container::getInstance()->isSingleton($name);
    }
}