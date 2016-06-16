<?php
/**
 * This file is part of the Trident package.
 * 
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Trident\Component\Stream;


class LIFOBufferStream extends BufferStream
{
    public function read($length = 1024)
    {
        $length = abs($length);
        $currentLength = strlen($this->buffer);
        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result = substr($this->buffer, $length);
            $this->buffer = substr($this->buffer, 0, $length);
        }

        return strrev($result);
    }
}