<?php

namespace React\Nntp\Response;

interface MultilineResponseInterface
{
    /**
     * Append data to the already received data.
     *
     * @param string $data The received data to append.
     */
    public function appendData($data);

    /**
     * Get the lines of the multiline response.
     *
     * @return array
     */
    public function getLines();

    /**
     * Check if the response received all lines.
     *
     * @return Boolean
     */
    public function isFinished();
}
