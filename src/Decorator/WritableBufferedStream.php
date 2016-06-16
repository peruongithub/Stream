<?php
/**
 * This file is part of the Trident package.
 * 
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream\Decorator;

use Psr\Http\Message\StreamInterface;
use Trident\Component\Stream\BufferStream;
use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Trident\Component\Stream\Traits\SeekableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\WritableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\ReadableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\MetadataStreamDecoratorTrait;

class WritableBufferedStream implements StreamInterface, ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface
{
    use MetadataStreamDecoratorTrait;
    use SeekableStreamDecoratorTrait;
    use ReadableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;
    /**
     * @var WritableStreamInterface
     */
    protected $stream;

    /**
     * @var BufferStream
     */
    protected $buffer;

    public function __construct(WritableStreamInterface $stream, BufferStream $buffer)
    {
        if (!$stream->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }
        $this->stream = $stream;
        $this->buffer = $buffer;
    }

    public function __destruct()
    {
        $this->stream->write($this->buffer->getContents());
        $this->stream->close();
    }

    public function close()
    {
        $this->stream->write($this->buffer->getContents());
        $this->buffer->close();
        $this->stream->close();
    }

    public function detach()
    {
        $this->stream->write($this->buffer->getContents());
        $this->buffer->detach();
        return $this->stream->detach();
    }

    public function writeStream(ReadableStreamInterface $stream)
    {
        $written = 0;
        while (!$stream->eof()) {
            $write = $this->write($stream->read(1024));
            if ($write === false) {
                return $written;
            }
            $written += $write;
        }

        return $written;
    }

    public function write($string)
    {
        $writtenTotal = 0;
        while (strlen($string)) {
            $written = $this->buffer->write($string);
            $writtenTotal += $written;
            if (!$written) {//flush buffer
                $flag = $this->stream->write($this->buffer->getContents());
                if (false === $flag) {
                    return $writtenTotal;
                    //throw new \RuntimeException('Stream is not writable.');
                }
            }
            $string = substr($string, $written);
        }

        return $writtenTotal;
    }
}