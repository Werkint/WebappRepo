<?php
namespace Werkint\Model;

class Package
{
    protected $id;
    protected $class;
    protected $title;
    protected $files = [
        'res'     => [],
        'scripts' => [],
    ];

    public function __construct(
        $id = null
    ) {
        $this->id = $id;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
}