<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream\Interfaces;

interface WritableStreamInterface extends SeekableStreamInterface, CloseableStreamInterface
{
    /**
     * Indicates whether the mode allows to write
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Write data to the stream from another stream.
     *
     * @param ReadableStreamInterface $stream
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function writeStream(ReadableStreamInterface $stream);

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string);
}
