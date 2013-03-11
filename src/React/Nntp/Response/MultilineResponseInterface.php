<?php

namespace React\Nntp\Response;

/**
 * MultilineResponseInterface
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface MultilineResponseInterface extends ResponseInterface
{
    public function getLines();
}
