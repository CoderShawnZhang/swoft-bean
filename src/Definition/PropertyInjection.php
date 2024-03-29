<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/10
 * Time: 上午10:27
 */

namespace SwoftRewrite\Bean\Definition;


class PropertyInjection
{
    /**
     * Property name.
     *
     * @var string
     */
    private $propertyName;

    /**
     * Value that should be injected in the property.
     *
     * @var mixed
     */
    private $value;

    /**
     * Is reference
     *
     * @var bool
     */
    private $isRef = false;

    /**
     * PropertyInjection constructor.
     *
     * @param string $propertyName
     * @param mixed  $value
     * @param bool   $isRef
     */
    public function __construct(string $propertyName, $value, bool $isRef)
    {
        $this->isRef        = $isRef;
        $this->value        = $value;
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isRef(): bool
    {
        return $this->isRef;
    }
}