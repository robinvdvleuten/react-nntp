<?php

/*
 * This file is part of the React NNTP component.
 *
 * (c) Robin van der Vleuten <robinvdvleuten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rvdv\React\Nntp\Command;

use Rvdv\React\Nntp\Group;
use Rvdv\React\Nntp\Response\ResponseInterface;
use React\Stream\Stream;

/**
 * GroupCommand
 *
 * The GROUP command selects a newsgroup as the currently selected
 * newsgroup and returns summary information about it.
 *
 * http://tools.ietf.org/html/rfc3977#section-6.1.1
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class GroupCommand extends Command implements CommandInterface
{
    /**
     * @var \React\Nntp\Group
     */
    private $group;

    /**
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param \React\Stream\Stream $stream A Stream instance.
     * @param string               $name   The name of the group.
     */
    public function __construct(Stream $stream, $name)
    {
        $this->name = $name;

        parent::__construct($stream);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        return $this->end("GROUP ".$this->name."\r\n");
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
        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::GROUP_SUCCESSFULLY_SELECTED => array(
                $this, 'handleGroupSelectedResponse',
            ),
            ResponseInterface::NO_SUCH_NEWSGROUP => array(
                $this, 'handleErrorResponse',
            ),
        );
    }

    /**
     * Handles the received group response from the NNTP server.
     * @see \React\Nntp\Command\GroupCommand::getResponseHandlers()
     *
     * @param \React\Response\ResponseInterfance $response A Response instance.
     */
    public function handleGroupSelectedResponse(ResponseInterface $response)
    {
        list($count, $first, $last, $name) = explode(' ', $response->getMessage());
        $this->group = new Group($name, $count, $first, $last);
    }
}
