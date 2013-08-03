<?php

/*
 * This file is part of React NNTP.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Command;

use Rvdv\React\Nntp\Response\ResponseInterface;

/**
 * CommandInterface
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
interface CommandInterface
{
    /**
     * Gets the response object.
     *
     * @return React\Nntp\Response\ResponseInterface A ResponseInterface instance.
     */
    public function getResponse();

    /**
     * Sets the response object.
     *
     * @param React\Nntp\Response\ResponseInterface $response A ResponseInterface instance.
     */
    public function setResponse(ResponseInterface $response);

    /**
     * Gets the command as string for exection.
     *
     * @return string The command as string.
     */
    public function execute();

    /**
     * Returns a boolean indicating if the response is multiline.
     *
     * @return boolean A boolean flagging the response as multiline.
     */
    public function expectsMultilineResponse();

    /**
     * Gets all response handlers for this command.
     *
     * @return array An index-value array containing all response handlers.
     */
    public function getResponseHandlers();

    /**
     * Gets the result when the command is executed.
     *
     * @return mixed The result of the command.
     */
    public function getResult();
}
