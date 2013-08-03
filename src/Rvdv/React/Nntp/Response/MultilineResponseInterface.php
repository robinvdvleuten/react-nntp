<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Response;

/**
 * MultilineResponseInterface
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface MultilineResponseInterface extends ResponseInterface
{
    public function getLines();
}
