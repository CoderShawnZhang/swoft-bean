<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/5
 * Time: 下午9:36
 */
use \SwoftRewrite\Annotation\AnnotationRegister;
use SwoftRewrite\Bean\BeanFactory;
require dirname(__DIR__) . '/vendor/autoload.php';

if($_SERVER['REQUEST_URI'] == '/favicon.ico'){
    return false;
}

AnnotationRegister::load(
    [
    ]
);


$definitions = getDefinitions();
$parsers = AnnotationRegister::getParsers();
$annotations = AnnotationRegister::getAnnotations();

BeanFactory::addAnnotations($annotations);
BeanFactory::addParsers($parsers);


function getDefinitions()
{
    $definitions = [];
    $autoLoaders = AnnotationRegister::getAutoLoaders();
    $disabledLoaders = [];
    foreach($autoLoaders as $autoLoader){
        //只处理autoloader 实现了 DefinitionInterface类，，，，这个就是面向接口编程

        if(!$autoLoader instanceof \SwoftRewrite\Bean\contract\DefinitionInterface){
            continue;
        }
        $autoLoader = get_class($autoLoader);

        if($autoLoader instanceof \SwoftRewrite\Bean\contract\ComponentInterface){
            continue;
        }
        $definitions = \SwoftRewrite\Stdlib\Helper\ArrayHelper::merge($definitions,$autoLoader->bean());
    }


}
