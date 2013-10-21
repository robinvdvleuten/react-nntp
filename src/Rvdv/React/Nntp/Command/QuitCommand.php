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
 * QuitCommand
 *
 * The client uses the QUIT command to terminate the session.
 *
 * http://tools.ietf.org/html/rfc3977#section-5.4
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class QuitCommand extends Command implements CommandInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("QUIT\r\n");
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::CONNECTION_CLOSING => array(
                $this, 'handleConnectionClosingResponse',
            ),
            ResponseInterface::SYNTAX_ERROR_IN_COMMAND => array(
                $this, 'handleErrorResponse',
            ),
        );
    }

    /**
     * Handles the received connection closing response from the NNTP server.
     * @see \React\Nntp\Command\QuitCommand::getResponseHandlers()
     *
     * @param \React\Response\ResponseInterfance $response A Response instance.
     */
    public function handleConnectionClosingResponse(ResponseInterface $response)
    {
        // We do nothing with the response here.
        return;
    }
}
