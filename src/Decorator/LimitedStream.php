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
use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Trident\Component\Stream\Traits\MetadataStreamDecoratorTrait;
use Trident\Component\Stream\Traits\ReadableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\SeekableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\WritableStreamDecoratorTrait;

/**
 * Decorator used to return only a subset of a stream
 */
class LimitedStream implements StreamInterface, ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface
{
    use ReadableStreamDecoratorTrait;
    use SeekableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;
    use MetadataStreamDecoratorTrait;
    /**
     * @var ReadableStreamInterface
     */
    protected $stream;
    protected $startPos;
    protected $endPos;
    protected $size = 0;

    /**
     * @param ReadableStreamInterface $stream
     * @param int $offset
     * @param int $whence
     */
    public function __construct(ReadableStreamInterface $stream, $offset, $whence = SEEK_SET)
    {
        if (!$stream->isSeekable()) {
            throw new \InvalidArgumentException('Stream must be seekable');
        }
        if (!$stream->isReadable()) {
            throw new \InvalidArgumentException('Stream must be readable');
        }
        $this->stream = $stream;
        $this->size = $stream->getSize();
        switch ($whence) {
            case SEEK_SET:
                $this->startPos = 0;
                $this->endPos = $offset;
                break;
            case SEEK_CUR:
                $this->startPos = $stream->tell();
                $this->endPos = $this->startPos + $offset;
                break;
            case SEEK_END:
                if ($offset <= 0) {
                    $this->startPos = $this->size + $offset;
                    $this->endPos = $this->size;
                } else {
                    throw new \InvalidArgumentException('Stream not support positive offset with SEEK_END.');
                }
                break;
        }
        if ($this->endPos >= $this->size) {
            $this->endPos = $this->size;
        }
        if ($this->startPos <= 0) {
            $this->startPos = 0;
        }
        $this->stream->seek($this->startPos, SEEK_SET);
    }

    public function detach()
    {
        $this->startPos = $this->endPos = $this->size = null;
        return $this->stream->detach();
    }

    public function close()
    {
        $this->startPos = $this->endPos = $this->size = null;
        $this->stream->close();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function isSeekable()
    {
        return true;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable(){
        return false;
    }

    public function rewind()
    {
        return $this->stream->seek($this->startPos, SEEK_SET);
    }

    public function eof()
    {
        return $this->stream->tell() == $this->endPos;
    }

    /**
     * Returns the size of the limited subset of data
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->endPos - $this->startPos;
    }

    /**
     * Allow for a bounded seek on the read limited stream
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $pos = $this->startPos;
        switch ($whence) {
            case SEEK_SET:
                $pos = $this->startPos + $offset;
                break;
            case SEEK_CUR:
                $pos = $this->stream->tell() + $offset;
                break;
            case SEEK_END:
                if ($offset <= 0) {
                    $pos = $this->endPos + $offset;
                } else {
                    return false;
                }
                break;
        }
        if ($pos >= $this->endPos) {
            $pos = $this->endPos;
        }
        if ($pos <= $this->startPos) {
            $pos = $this->startPos;
        }

        return $this->stream->seek($pos, SEEK_SET);
    }

    public function read($length = 1024)
    {
        $current = $this->stream->tell();
        if (!($current >= $this->startPos && $current < $this->endPos)) {
            return false;
        }
        if (($current + $length) > $this->endPos) {
            $length = $this->endPos - $current;
        }

        return $this->stream->read($length);
    }
}