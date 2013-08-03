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
 * Article
 *
 * @author Robin van der Vleuten <robinvdvleuten@gmail.com>
 */
class Article
{
    private $number;

    private $subject;

    private $from;

    private $date;

    private $messageId;

    private $references;

    private $bytes;

    private $lines;

    private $xref;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function setReferences($references)
    {
        $this->references = $references;
    }

    public function getBytes()
    {
        return $this->bytes;
    }

    public function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    public function getLines()
    {
        return $this->lines;
    }

    public function setLines($lines)
    {
        $this->lines = $lines;
    }

    public function getXref()
    {
        return $this->xref;
    }

    public function setXref($xref)
    {
        $this->xref = $xref;
    }
}
