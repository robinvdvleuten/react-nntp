<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Exception;

use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * NotImplementedMethodException
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class NotImplementedMethodException extends \RuntimeException
{
    /**
     * Constructor.
     */
    public function __construct(method)
    {
        parent::__construct(sprintf('%s is not implemented yet', $method));
    }
}
