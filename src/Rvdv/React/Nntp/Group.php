<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp;

/**
 * Group
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
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
