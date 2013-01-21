<?php

namespace React\NNTP;

class Group
{
    protected $count;
    protected $first;
    protected $last;
    protected $name;

    public function __construct($name, $count, $first, $last)
    {
        $this->name = $name;
        $this->count = $count;
        $this->first = $first;
        $this->last = $last;
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
