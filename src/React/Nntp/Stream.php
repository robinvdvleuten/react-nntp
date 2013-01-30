<?php

namespace React\Nntp;

use React\Stream\Stream as BaseStream;

class Stream extends BaseStream
{
    public function handleData($stream)
    {
        $data = fgets($stream, $this->bufferSize);
        $this->emit('data', array($data, $this));

        if (!is_resource($stream) || feof($stream)) {
            $this->end();
        }
    }
}
