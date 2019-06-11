<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/5
 * Time: 下午9:00
 */
namespace SwoftRewrite\Bean\Definition\Parser;

class ObjectParser
{
    protected $definitions = [];
    protected $objectDefinitions = [];
    protected $classNames = [];
    protected $aliases = [];

    /**
     * ObjectParser constructor.
     * @param array $definitions
     * @param array $objectDefinitions
     * @param array $classNames
     * @param array $aliases
     */
    public function __construct(array $definitions,array $objectDefinitions,array $classNames,array $aliases)
    {
        $this->definitions = $definitions;
        $this->objectDefinitions = $objectDefinitions;
        $this->classNames = $classNames;
        $this->aliases = $aliases;
    }

    protected function getValueByRef($value)
    {
        if(!is_string($value)){
            return  [$value,false];
        }
        // Reg match
        $isRef = preg_match('/^\$\{(.*)\}$/', $value, $match);
        if ($isRef && isset($match[1])) {
            return [$match[1], (bool)$isRef];
        }

        return [$value, false];
    }
}