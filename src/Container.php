<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/5
 * Time: 下午8:42
 */
namespace SwoftRewrite\Bean;

use SwoftRewrite\Bean\Annotation\Mapping\Bean;
use SwoftRewrite\Bean\Definition\ArgsInjection;
use SwoftRewrite\Bean\Definition\MethodInjection;
use SwoftRewrite\Bean\Definition\ObjectDefinition;
use SwoftRewrite\Bean\Definition\Parser\AnnotationObjParser;
use SwoftRewrite\Bean\Definition\Parser\DefinitionObjeParser;
use SwoftRewrite\Bean\Definition\PropertyInjection;
use SwoftRewrite\Stdlib\Helper\ArrayHelper;

class Container
{
    public const INIT_METHOD = 'init';

    /**
     * @var Container $instance;
     */
    public static $instance;

    private $definitions = [];
    private $annotations = [];
    private $parsers = [];
    private $aliases = [];

    private $objectDefinitions = [];
    private $classNames = [];

    private $requestDefinitions = [];
    private $sessionDefinitions = [];

    private $singletonPool = [];
    private $prototypePool = [];
    private $requestPool = [];
    private $sessionPool = [];

    private $handler;

    public function init()
    {
        $this->parseAnnotations();
        $this->parseDefinitions();
        $this->initializeBeans();
    }

    public function get($id)
    {
        if(isset($this->singletonPool[$id])){
            return $this->singletonPool[$id];
        }

        //Prototype by clone
        if(isset($this->prototypePool[$id])){
            return clone $this->prototypePool[$id];
        }

        //Alias name
        $aliasId = $this->aliases[$id] ?? [];
        if($aliasId){
            return $this->get($aliasId);
        }

        //class name
        $classNames = $this->classNames[$id] ?? [];
        if($classNames){
            $id = end($classNames);
            return $this->get($id);
        }
        //not defined
        if(!isset($this->objectDefinitions[$id])){
            throw new \Exception(sprintf('The bean of %s is not defined',$id));
        }
        /* @var ObjectDefinition $objectDefintion */
        $objectDefintion = $this->objectDefinitions[$id];

        //prototype
        return $this->newBean($objectDefintion->getName());
    }

    public function isSingleton(string $name)
    {
        if(isset($this->aliases[$name])){
            $name = $this->aliases[$name];
        }
        return isset($this->singletonPool[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getSingleton(string $name)
    {
        if(isset($this->singletonPool[$name])){
            return $this->singletonPool[$name];
        }

        if(isset($this->aliases[$name])){
            $name = $this->aliases[$name];
            return $this->singletonPool[$name];
        }

        $classNames = $this->classNames[$name] ?? [];
        if($classNames){
            $name = end($classNames);
            return $this->singletonPool[$name];
        }

        throw new \Exception(sprintf('The singleton bean "%s" is not defined',$name));
    }

    public function addDefinitions(array $definitions)
    {
        $this->definitions = ArrayHelper::merge($this->definitions,$definitions);
    }

    public function addAnnotations(array $annotations)
    {
        $this->annotations = ArrayHelper::merge($this->annotations,$annotations);
    }

    public function addParsers(array $parsers)
    {
        $this->parsers = ArrayHelper::merge($this->parsers,$parsers);
    }

    public static function getInstance()
    {
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }


    private function parseAnnotations()
    {
        $annotationParser = new AnnotationObjParser($this->definitions,$this->objectDefinitions,$this->classNames,$this->parsers);
        $annotationData = $annotationParser->parseAnnotations($this->annotations,$this->parsers);
        [$this->definitions,$this->objectDefinitions,$this->classNames,$this->aliases] = $annotationData;
    }

    private function parseDefinitions()
    {
        $annotationParser = new DefinitionObjeParser($this->definitions,$this->objectDefinitions,$this->classNames,$this->parsers);
        $definitionData = $annotationParser->parseDefinitions();
        [$this->definitions,$this->objectDefinitions,$this->classNames,$this->aliases] = $definitionData;
    }

    private function initializeBeans()
    {
        /* @var ObjectDefinition $objectDefinition */
        foreach($this->objectDefinitions as $beanName => $objectDefinition){
            $scope = $objectDefinition->getScope();
            //$objectDefinition 定义里不包括 request
            if($scope === Bean::REQUEST){
                $this->requestDefinitions[$beanName] = $objectDefinition;
                unset($this->objectDefinitions[$beanName]);
                continue;
            }
            //$objectDefinition 定义里不包括 session
            if($scope === Bean::SESSION){
                $this->sessionDefinitions[$beanName] = $objectDefinition;
                unset($objectDefinition[$beanName]);
                continue;
            }
            $this->newBean($beanName);
        }
    }

    private function newBean(string $beanName,string $id='')
    {
        $objectDefinition = $this->getNewObjectDefinition($beanName);

        $scope = $objectDefinition->getScope();
        $alias = $objectDefinition->getAlias();
        $className = $objectDefinition->getClassName();

        $constructArgs = [];
        $constructInject = $objectDefinition->getConstructorInjection();
        if($constructInject !== null){
            $constructArgs = $this->getConstructParams($constructInject,$id);
        }

        $propertyInjects = $objectDefinition->getPropertyInjections();
        if($this->handler){
            $className = $this->handler->classProxy($className);
        }
        $reflectionClass = new \ReflectionClass($className);
        $reflectObject = $this->newInstance($reflectionClass,$constructArgs);
        $this->newProperty($reflectObject,$reflectionClass,$propertyInjects,$id);

        if(!empty($alias)){
            $this->aliases[$alias] = $beanName;
        }
        if($reflectionClass->hasMethod(self::INIT_METHOD)){
            $reflectObject->{self::INIT_METHOD}();
        }
        return $this->setNewBean($beanName,$scope,$reflectObject,$id);
    }

    private function getNewObjectDefinition(string $beanName): ObjectDefinition
    {
        if(isset($this->objectDefinitions[$beanName])){
            return $this->objectDefinitions[$beanName];
        }

        if(isset($this->requestDefinitions[$beanName])){
            return $this->requestDefinitions[$beanName];
        }

        if(isset($this->sessionDefinitions[$beanName])){
            return $this->sessionDefinitions[$beanName];
        }

        $classNames = $this->classNames[$beanName] ?? [];
        if(!empty($classNames)){
            $beanName = end($classNames);
            return $this->getNewObjectDefinition($beanName);
        }

        if(isset($this->aliases[$beanName])){
            return $this->getNewObjectDefinition($this->aliases[$beanName]);
        }
        throw new \Exception('Bean name of ' . $beanName . ' is not defined');
    }

    private function getConstructParams(MethodInjection $methodInjection,string $id = '')
    {
        $methodName = $methodInjection->getMethodName();
        if($methodName !== '__construct'){
            throw new \Exception('ConstructInjection method must be `__construct`');
        }
        $argInjects = $methodInjection->getParameters();
        if(empty($argInjects)){
            return [];
        }
        $args = [];
        foreach($argInjects as $argInject){
            $argValue = $argInject->getValue();
            if(empty($argValue) || !is_string($argValue)){
                $args[] = $argValue;
                continue;
            }

            $isRef = $argInject->isRef();
            if($isRef){
                $argValue = $this->getRefValue($argValue,$id);
            }
            $args[] = $argValue;
        }
        return $args;
    }

    private function getRefValue($value,string $id = '')
    {
        if(!is_string($value)){
            return $value;
        }
        if(false === strpos($value,'.')){
            return $this->newBean($value,$id);
        }
        if($this->handler !== null){
            $value = $this->handler->getreferenceValue($value);
        }
        return $value;
    }

    private function newInstance(\ReflectionClass $reflectionClass,array $args)
    {
        if(empty($args) || !$reflectionClass->hasMethod('__construct')){
            return $reflectionClass->newInstance();
        }
        $reflectMethod = $reflectionClass->getMethod('__construct');
        if($reflectMethod->isPrivate() || $reflectMethod->isProtected()){
            throw new \Exception('Construct function for bean must be public!');
        }
        return $reflectionClass->newInstanceArgs($args);
    }

    private function newProperty($reflectionObject,\ReflectionClass $reflectionClass,array $propertyInjects, string $id = '')
    {
        $parentClass = $reflectionClass->getParentClass();
        if($parentClass !== false){
            $this->newProperty($reflectionClass,$parentClass,$propertyInjects,$id);
        }
        /* @var PropertyInjection $propertyInject */
        foreach($propertyInjects as $propertyInject){
            $propertyName = $propertyInject->getPropertyName();
            if(!$reflectionClass->hasProperty($propertyName)){
                continue;
            }
            $reflectProperty = $reflectionClass->getProperty($propertyName);
            if($reflectProperty->isStatic()){
                throw new \Exception(sprintf('Property %s for bean can not be `static` ',$propertyName));
            }
            $propertyValue = $propertyInject->getValue();
            if(is_array($propertyValue)){
                $propertyValue = $this->newPropertyArray($propertyValue,$id);
            }
            if($propertyInject->isRef()){
                $propertyValue = $this->getRefValue($propertyValue,$id);
            }
            $setter = 'set' . ucfirst($propertyName);
            if(method_exists($reflectionObject,$setter)){
                $reflectionObject->$setter($propertyValue);
                continue;
            }
            if(!$reflectProperty->isPublic()){
                $reflectProperty->setAccessible(true);
            }
            $reflectProperty->setValue($reflectionObject,$propertyValue);
        }
    }

    private function newPropertyArray(array $propertyValue,string $id = '')
    {
        foreach($propertyValue as $proKey => &$proValue){
            if($proValue instanceof ArgsInjection && $proValue->isRef()){
                $refValue = $proValue->getValue();
                $proValue = $this->getRefValue($refValue,$id);
            }
        }
        return $propertyValue;
    }

    private function setNewBean(string $beanName,string $scope,$object,string $id = '')
    {
        switch ($scope) {
            case Bean::SINGLETON:
                $this->singletonPool[$beanName] = $object;
                break;
            case Bean::PROTOTYPE:
                $this->prototypePool[$beanName] = $object;
                $object = clone $object;
                break;
            case Bean::REQUEST:
                $this->requestPool[$id][$beanName] = $object;
                break;
            case Bean::SESSION:
                $this->sessionPool[$id][$beanName] = $object;
                break;
        }

        return $object;
    }
}