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

use Rvdv\React\Nntp\Group;
use Rvdv\React\Nntp\Response\MultilineResponseInterface;
use Rvdv\React\Nntp\Response\ResponseInterface;
use React\Stream\Stream;
use RuntimeException;

/**
 * ListCommand
 *
 * The LIST command allows the server to provide blocks of information to the client.
 *
 * http://tools.ietf.org/html/rfc3977#section-7.6.1
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class ListCommand extends Command implements CommandInterface
{
    const KEYWORD_ACTIVE = 'ACTIVE';

    const KEYWORD_ACTIVE_TIMES = 'ACTIVE.TIMES';

    const KEYWORD_DISTRIB_PATS = 'DISTRIB.PATS';

    const KEYWORD_HEADERS = 'HEADERS';

    const KEYWORD_NEWSGROUPS = 'NEWSGROUPS';

    const KEYWORD_OVERVIEW_FMT = 'OVERVIEW.FMT';

    /**
     * @var array
     */
    private $groups;

    /**
     * @var string
     */
    private $keyword;

    /**
     * @var string
     */
    private $wildmat;

    /**
     * Constructor.
     *
     * @param \React\Stream\Stream $stream  A Stream instance.
     * @param string               $keyword The keyword of the block of information.
     * @param string               $wildmat Limit the returned groups by the wildmat.
     */
    public function __construct(Stream $stream, $keyword = null, $wildmat = null)
    {
        $this->keyword = $keyword;
        $this->wildmat = $wildmat;

        parent::__construct($stream);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $command = strtoupper($this->keyword);

        if ($this->wildmat) {
            $command .= ' ' . $this->wildmat;
        }

        return $this->end("LIST $command\r\n");
    }

    /**
     * {@inheritDoc}
     */
    public function expectsMultilineResponse()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return $this->groups;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHandlers()
    {
        return array(
            ResponseInterface::GROUPS_FOLLOW => array(
                $this, 'handleGroupsFollowResponse'
            ),
            ResponseInterface::SYNTAX_ERROR_IN_COMMAND => array(
                $this, 'handleErrorResponse',
            ),
            ResponseInterface::NOT_SUPPORTED => array(
                $this, 'handleErrorResponse',
            ),
        );
    }

    public function handleGroupsFollowResponse(MultilineResponseInterface $response)
    {
        $this->groups = array();

        foreach ($response->getLines() as $line) {
            $parts = explode(' ', $line);

            $group = new Group();
            $group->setName($parts[0]);

            switch ($this->keyword) {
                case self::KEYWORD_ACTIVE:
                default:
                    $group->setLast($parts[1])
                          ->setFirst($parts[2])
                          ->setActive($parts[3] === 'y' ? true : false);
                    break;
                case self::KEYWORD_ACTIVE_TIMES:
                    $group->setCreated($parts[1])
                          ->setCreatedBy($parts[2]);
                    break;
                case self::KEYWORD_DISTRIB_PATS:
                case self::KEYWORD_HEADERS:
                case self::KEYWORD_NEWSGROUPS:
                case self::KEYWORD_OVERVIEW_FMT:
                    throw new RuntimeException('The ' . $this->keyword . 'keyword is currently not implemented.');
                    break;
            }

            if ($group) {
                $this->groups[] = $group;
            }
        }
    }
}
