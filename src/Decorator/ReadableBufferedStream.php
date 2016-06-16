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
use Trident\Component\Stream\Traits\ReadableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\SeekableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\WritableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\MetadataStreamDecoratorTrait;


class ReadableBufferedStream implements StreamInterface, ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface
{
    use MetadataStreamDecoratorTrait{
        getMetadata as protected getTraitMetadata;
    }
    use SeekableStreamDecoratorTrait;
    use ReadableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;

    const BUFFER_FULL = 2;
    const BUFFER_READY = 1;
    const BUFFER_EOF = 0;
    /**
     * @var ReadableStreamInterface
     */
    protected $stream;

    /**
     * @var BufferStream
     */
    protected $readBuffer;

    protected $hwm;

    protected $fillingBufferPortion = 1024;

    /**
     * @var \Generator
     */
    protected $generator;

    public function __construct(ReadableStreamInterface $stream, BufferStream $readBuffer)
    {
        if (!$stream->isReadable()) {
            throw new \RuntimeException('Stream is not readable.');
        }
        $this->stream = $stream;
        $this->readBuffer = $readBuffer;
        $this->hwm = $this->readBuffer->getMetadata('hwm');
        $this->generator = $this->getGenerator();
    }

    public function __destruct()
    {
        $this->readBuffer->close();
        $this->stream->close();

    }

    public function close()
    {
        $this->readBuffer->close();
        $this->stream->close();
        $this->generator = $this->stream = $this->readBuffer = $this->hwm = null;
    }

    public function detach()
    {
        $this->readBuffer->detach();
        $this->stream->detach();
        $this->generator = $this->stream = $this->readBuffer = $this->hwm = null;
    }

    public function read($length = 1024)
    {
        $syg = $this->generator->current();

        $length = $this->hwm >= $length ? $length : $this->hwm;

        if (self::BUFFER_FULL === $syg) {
            //do something
        } elseif (self::BUFFER_EOF === $syg && $this->readBuffer->eof()) {
            return false;
        } else {
            $this->generator->next();
        }

        return $this->readBuffer->read($length);
    }


    /**
     * @return \Generator
     */
    protected function getGenerator()
    {
        try {
            while (true) {
                $toWrite = '';//internal cache

                while (!null === $toWrite) {
                    //don't read the stream when stream is eof add internal cache not empty
                    if (!$this->stream->eof() && !strlen($toWrite)) {
                        $toWrite = $this->stream->read($this->fillingBufferPortion);
                        if (!$toWrite) {
                            throw new \RuntimeException('Can not read stream.');
                        }
                    }
                    $len = strlen($toWrite);
                    if (!$len) {
                        $toWrite = null;
                        yield self::BUFFER_EOF;
                    }

                    $written = $this->readBuffer->write($toWrite);

                    if ($this->hwm == $this->readBuffer->getSize()) {
                        yield self::BUFFER_FULL;
                    }

                    if (0 == $written) {
                        //prevent substr()
                    } elseif ($written < $len) {
                        $toWrite = substr($toWrite, $written);
                    } elseif ($written == $len) {
                        $toWrite = '';
                        continue;
                    }

                    //yield self::BUFFER_READY;
                }
            }
        } finally {
            // http://chat.stackoverflow.com/transcript/message/7727858#7727858
        }
    }

    public function getMetadata($key = null)
    {
        if ('unread_bytes' === $key) {
            return $this->readBuffer->getSize();
        }

        return $this->getTraitMetadata($key);
    }
}