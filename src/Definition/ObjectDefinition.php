<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/9
 * Time: 上午11:01
 */
namespace SwoftRewrite\Bean\Definition;

use SwoftRewrite\Bean\Annotation\Mapping\Bean;

class ObjectDefinition
{
    private $name;
    private $className;
    private $scope = Bean::SINGLETON;
    private $alias = '';


    private $propertyInjections = [];
    private $methodInjections = [];
    private $constructorInjection;


    public function __construct(string $name,string $className,string $scope = Bean::SINGLETON,string $alias = '')
    {
        $this->name = $name;
        $this->scope = $scope;
        $this->alias = $alias;
        $this->className = $className;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    public function getPropertyInjections()
    {
        return $this->propertyInjections;
    }


    public function setPropertyInjections(array $propertyInjections)
    {
        $this->propertyInjections = $propertyInjections;
    }

    public function setMethodInjections(array $methodInjections)
    {
        $this->methodInjections = $methodInjections;
    }

    public function setConstructorInjection(MethodInjection $constructorInjection)
    {
        $this->constructorInjection = $constructorInjection;
    }

    public function setPropertyInjection(string $propertyName,PropertyInjection $propertyInjetion)
    {
        $this->propertyInjections[$propertyName] = $propertyInjetion;
    }

    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }

    public function setAlias(string $alias)
    {
        $this->alias = $alias;
    }
}