<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/10
 * Time: 上午9:35
 */

namespace SwoftRewrite\Bean\Definition\Parser;


use MongoDB\BSON\Unserializable;
use SwoftRewrite\Bean\Annotation\Mapping\Bean;
use SwoftRewrite\Bean\Definition\ArgsInjection;
use SwoftRewrite\Bean\Definition\MethodInjection;
use SwoftRewrite\Bean\Definition\ObjectDefinition;
use SwoftRewrite\Bean\Definition\PropertyInjection;

class DefinitionObjeParser extends ObjectParser
{
    public function parseDefinitions()
    {
        //$this->definitions  用户在 bean 定义的
        foreach($this->definitions as $beanName => $definition){
            if(isset($this->objectDefinitions[$beanName])){
                $objectDefinition = $this->objectDefinitions[$beanName];
                //‌‌$beanName:
                //‌DemoT

                //‌‌$objectDefinition
                //‌SwoftRewrite\Bean\Definition\ObjectDefinition::__set_state(array(
                //   'name' => 'DemoT',
                //   'className' => 'SwoftRewrite\\Bean\\DemoT',
                //   'scope' => 'singleton',
                //   'alias' => '',
                //   'propertyInjections' =>
                //  array (
                //  ),
                //   'methodInjections' =>
                //  array (
                //  ),
                //))

                //‌‌$definition
                //‌array (
                //  'class' => 'SwoftRewrite\\Console\\Application',
                //  'version' => '1.0.0',
                //)
                $this->resetObjectDefinition($beanName,$objectDefinition,$definition);
                continue;
            }
            //没有定义就创建定义
            $this->createObjectDefinition($beanName,$definition);
        }
        return [$this->definitions,$this->objectDefinitions,$this->classNames,$this->aliases];
    }

    private function resetObjectDefinition(string $beanName,ObjectDefinition $objDefinition, array $definition)
    {
        $className = $definition['class'] ?? '';
        $objClassName = $objDefinition->getClassName();
        if(!empty($className) && $className !== $objClassName){
            throw new \Exception('Class for annotations and definitions must be the same Or not to define class');
        }
        $objDefinition = $this->updateObjectDefinitionByDefinition($objDefinition,$definition);
        $this->objectDefinitions[$beanName] = $objDefinition;
    }
    private function updateObjectDefinitionByDefinition(ObjectDefinition $objDfn,array $definition)
    {
        [$constructInject,$propertyInjects,$option] = $this->parseDefinition($definition);
        if(!empty($constructInject)){
            $objDfn->setMethodInjections($constructInject);
        }
        foreach($propertyInjects as $propertyName => $propertyInject){
            $objDfn->setPropertyInjection($propertyName,$propertyInject);
        }
        $scopes = [
            Bean::SINGLETON,
            Bean::PROTOTYPE,
            Bean::REQUEST,
        ];
        $scope = $option['scope'] ?? '';
        $alias = $option['alias'] ?? '';
        if(!empty($scope) && !in_array($scope,$scopes,true)){
            throw new \Exception('Scope for definition is not undefined');
        }
        if(!empty($scope)){
            $objDfn->setScope($scope);
        }
        if(!empty($alias)){
            $objDfn->setAlias($alias);
            $objAlias = $objDfn->getAlias();
            unset($this->aliases[$objAlias]);
            $this->aliases[$alias] = $objDfn->getName();
        }
        return $objDfn;
    }
    private function parseDefinition(array $definition)
    {
        unset($definition['class']);
        $constructArgs = $definition[0] ?? [];
        if(!is_array($constructArgs)){
            throw new \Exception('Construct args for definition must be array');
        }
        $argInjects = [];
        foreach($constructArgs as $arg){
            [$argValue,$argIsRef] = $this->getValueByRef($arg);
            $argInjects[] = new ArgsInjection();
        }

        $constructInject = null;
        if(!empty($argInjects)){
            $constructInject = new MethodInjection('__construct',$argInjects);
        }
        unset($definition[0]);
        $option = $definition['__option'] ?? [];
        if(!is_array($option)){
            throw new \Exception('__option for definition must be array');
        }
        unset($definition['__option']);
        $propertyInjects = [];
        //$definition 去除其他的剩下的都是属性了
        foreach($definition as $propertyName => $propertyValue){
            if(!is_string($propertyName)){
                throw new \Exception('Property key from definition must be string');
            }
            [$proValue,$proIsRef] = $this->getValueByRef($propertyValue);

            //判断定义的属性是否是数组
            if(is_array($proValue)){
                $proValue = $this->parseArrayProperty($proValue);
            }
            $propertyInject = new PropertyInjection($propertyName,$proValue,$proIsRef);
            $propertyInjects[$propertyName] = $propertyInject;
        }
        return [$constructInject,$propertyInjects,$option];
    }
    private function parseArrayProperty(array $propertyValue)
    {
        foreach($propertyValue as $proKey =>&$proValue){
            [$refValue,$isRef] = $this->getValueByRef($proValue);
            if(!$isRef){
                continue;
            }
            $proValue = new ArgsInjection($refValue,$isRef);
        }
        return $propertyValue;
    }


    private function createObjectDefinition(string $beanName,array $definition)
    {
        $className = $definition['class'] ?? '';
        if(empty($className)){
            throw new \Exception(sprintf('%s key for definition must be defined class',$beanName));
        }
        $objDefinition = new ObjectDefinition($beanName,$className);
        $objDefinition = $this->updateObjectDefinitionByDefinition($objDefinition,$definition);

        $classNames = $this->classNames[$className] ?? [];
        $classNames[] = $beanName;
        $this->classNames[$className] = array_unique($classNames);
        $this->objectDefinitions[$beanName] = $objDefinition;
    }
}