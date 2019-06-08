<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/5
 * Time: 下午9:06
 */

class BeanProcessor
{
    private function getDefinitions()
    {
        $autoloader = \SwoftRewrite\Annotation\AnnotationRegister::getAutoLoader();
    }
}