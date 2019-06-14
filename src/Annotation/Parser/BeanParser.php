<?php
/**
 * 处理解析器解析到的类文件
 *
 */
namespace SwoftRewrite\Bean\Annotation\Parser;

use SwoftRewrite\Annotation\Annotation\Mapping\AnnotationParser;
use SwoftRewrite\Annotation\Annotation\Parser\Parser;
use SwoftRewrite\Bean\Annotation\Mapping\Bean;

/**
 * @AnnotationParser(Bean::class)
 */
class BeanParser extends Parser
{
    /**
     * 所有组件的注解 解释器都调用这个
     * @param int $type
     * @param Bean $annotationObject
     * @return array
     */
    public function parse(int $type, $annotationObject)
    {
        if($type != self::TYPE_CLASS){
            return [];
        }
        $name = $annotationObject->getName();
        $scope = $annotationObject->getScope();
        $alias = $annotationObject->getAlias();
        return [$name,$this->className,$scope,$alias];
    }
}