<?php

namespace React\Nntp;

class Group
{
    protected $active;
    protected $count;
    protected $first;
    protected $last;
    protected $name;

    public function __construct($name, $count, $first, $last, $active = true)
    {
        $this->name = $name;
        $this->count = $count;
        $this->first = $first;
        $this->last = $last;
        $this->active = $active;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getFirst()
    {
        return $this->first;
    }

    public function getLast()
    {
        return $this->last;
    }

    public function getName()
    {
        return $this->name;
    }
}
