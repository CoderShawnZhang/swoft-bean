<?php
/**
 * Created by PhpStorm.
 * User: zhanghongbo
 * Date: 2019/6/5
 * Time: 下午8:42
 */
namespace SwfotRewrite\Bean;

use SwoftRewrite\Stdlib\Helper\ArrayHelper;

class Container
{
    public static $instance;

    private $definitions = [];
    private $annotations = [];
    private $parsers = [];

    public function init()
    {
        $this->parseAnnotations();
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
}