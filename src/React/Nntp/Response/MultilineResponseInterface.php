<?php

namespace React\Nntp\Response;

interface MultilineResponseInterface
{
    /**
     * Append lines to the already received lines.
     */
    public function appendLines(array $lines);

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
