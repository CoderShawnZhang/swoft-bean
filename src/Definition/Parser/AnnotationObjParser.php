<?php
/**
 * 注释解析器
 */
namespace SwoftRewrite\Bean\Definition\Parser;

use SwoftRewrite\Annotation\Annotation\Parser\Parser;
use SwoftRewrite\Bean\Definition\ObjectDefinition;

class AnnotationObjParser extends ObjectParser
{
    private $annotations = [];
    private $parsers = [];

    public function parseAnnotations(array $annotations,array $parsers)
    {
        //‌‌$parsers:
        //‌array (
        //  9999999 => 'SwoftRewrite\\Console\\Annotation\\Parser\\CommmandParser',
        //)
        $this->parsers = $parsers;
        $this->annotations = $annotations;

        foreach($annotations as $loadNameSpace => $classes){
            foreach($classes as $className => $classOneAnnotations){
                $this->parseOneClassAnnotations($className,$classOneAnnotations);
            }
        }
        return [$this->definitions, $this->objectDefinitions, $this->classNames, $this->aliases];
    }

    private function parseOneClassAnnotations(string $className,array $classOneAnnotations)
    {
        if(!isset($classOneAnnotations['annotation'])){
            throw new \Exception('Property or method(%s) with `@xxx` must be define class annotation',$className);
        }
        //扫描 类注解
        $classAnnotations = $classOneAnnotations['annotation'];
        $reflectionClass = $classOneAnnotations['reflection'];

        $classAry = [
            $className,
            $reflectionClass,
            $classAnnotations
        ];

        $objectDefinition = $this->parseClassAnnotations($classAry);

        //扫描 属性注解
        $propertyInjects = [];
        $propertyAllAnnotations = $classAnnotations['properties'] ?? [];
        foreach ($propertyAllAnnotations as $propertyName => $prorpertyOneAnnotations)
        {
            $proAnnotations = $prorpertyOneAnnotations['annotation'] ?? [];
            $propertyInject = $this->parsePropertyAnnotations($classAry,$propertyName,$proAnnotations);
            if($propertyInject){
                $propertyInjects[$propertyName] = $propertyInject;
            }
        }

        //扫描 方法注解
        $methodInjects = [];
        $methodAllAnnotations = $classOneAnnotations['methods'] ?? [];
        foreach($methodAllAnnotations as $methodName => $methodOneAnnotations){
            $methodAnnotations = $methodAllAnnotations['annotation'] ?? [];
            $methodInject = $this->parseMethodAnnotations($classAry,$methodName,$methodAnnotations);
            if($methodInject){
                $methodInjects[$methodName] = $methodInject;
            }
        }
        if(!$objectDefinition){
            return;
        }
        if(!empty($propertyInjects)){
            $objectDefinition->setPropertyInjections($propertyInjects);
        }
        if(!empty($methodInjects)){
            $objectDefinition->setMethodInjections($methodInjects);
        }

        $name = $objectDefinition->getName();
        $aliase = $objectDefinition->getAlias();
        $classNames = $this->classNames[$className] ?? [];
        $classNames[] = $name;
        $this->classNames[$className] = array_unique($classNames);
//       $this->objectDefinitions  $name:‌DemoT
//        ‌SwoftRewrite\Bean\Definition\ObjectDefinition::__set_state(array(
//            'name' => 'DemoT',
//            'className' => 'SwoftRewrite\\Bean\\DemoT',
//            'scope' => 'singleton',
//            'alias' => '',
//            'propertyInjections' =>
//                array (
//                ),
//            'methodInjections' =>
//                array (
//                ),
//        ))
        $this->objectDefinitions[$name] = $objectDefinition;
        if(!empty($aliase)){
            $this->aliases[$aliase] = $name;
        }
    }

    private function parseClassAnnotations(array $classAry)
    {
        [,,$classAnnotations]  = $classAry;
        $objectDefinition = null;

        foreach($classAnnotations as $annotation){
            //$annotationClass : ‌Doctrine\Common\Annotations\Annotation\Target
            $annotationClass = get_class($annotation);

            if(!isset($this->parsers[$annotationClass])){
                continue;
            }
            $parserClassName = $this->parsers[$annotationClass];
            //$this->parsers存的是Mapping=>Parser
            $annotationParser = $this->getAnnotationParser($classAry,$parserClassName);
            $data = $annotationParser->parse(Parser::TYPE_CLASS,$annotation);
            if(empty($data)){
                continue;
            }
            if(count($data) !== 4){
                throw new \Exception(sprintf('%s annotation parse must be 4 size', $annotationClass));
            }
            [$name,$className,$scope,$alias] = $data;
            if(empty($className)){
                throw new \Exception(sprintf('%s with class name can not be empty', $annotationClass));
            }
            $objectDefinition = new ObjectDefinition($name,$className,$scope,$alias);
        }
        return $objectDefinition;
    }

    private function parsePropertyAnnotations($classAry,$propertyName,$proAnnotations)
    {

    }

    private function parseMethodAnnotations($classAry,$methodName,$methodAnnotations)
    {

    }
    /***/
    private function getAnnotationParser(array $classAry,string $parserClassName)
    {
        [$className,$reflectionClass,$classAnnotations] = $classAry;
        $annotationParser = new $parserClassName($className,$reflectionClass,$classAnnotations);
        return $annotationParser;
    }
}