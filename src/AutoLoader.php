<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/8
 * Time: 下午7:54
 */

namespace SwoftRewrite\Bean;


class AutoLoader extends \SwoftRewrite\Annotation\AutoLoader
{
    public function getPrefixDirs()
    {
        return [
          __NAMESPACE__ => __DIR__
        ];
    }
}