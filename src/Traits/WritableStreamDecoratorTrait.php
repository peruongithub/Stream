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

use Psr\Http\Message\StreamInterface;
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
        if($this->stream instanceof WritableStreamInterface || $this->stream instanceof StreamInterface){
            return $this->stream->isWritable();
        }
        return false;
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