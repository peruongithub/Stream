<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream\Traits;

use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;


trait WritableStreamDecoratorTrait
{
    /**
     * @var WritableStreamInterface
     */
    protected $stream;

    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    public function writeStream(ReadableStreamInterface $stream)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }
        return $this->stream->writeStream($stream);
    }

    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }
        return $this->stream->write($string);
    }
}