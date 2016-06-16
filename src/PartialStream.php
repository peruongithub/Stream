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

use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Psr\Http\Message\StreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Trident\Component\Stream\Traits\MetadataStreamTrait;

/**
 * Reads from multiple streams, one after the other.
 *
 * This is a read-only stream decorator.
 */
class PartialStream implements ReadableStreamInterface,
                                WritableStreamInterface,
                                MetadataStreamInterface,
                                StreamInterface
{
    use MetadataStreamTrait;
    /** @var ReadableStreamInterface[]
     */
    protected $streams = [];
    protected $totalPart = 0;
    protected $current = 0;
    protected $pos = 0;
    protected $size = 0;
    protected $map = [];

    /**
     * @param ReadableStreamInterface[] $streams Streams to decorate. Each stream must
     *                                   be readable and seekable.
     */
    public function __construct(array $streams = [])
    {
        foreach ($streams as $stream) {
            if (
                !$stream instanceof ReadableStreamInterface ||
                !$stream instanceof StreamInterface
            ) {
                throw new \InvalidArgumentException(
                    'Each stream must implement Psr\Http\Message\StreamInterface or Trident\Component\Stream\Interfaces\ReadableStreamInterface'
                );
            }

            if (!$stream->isReadable() || !$stream->isSeekable()) {
                throw new \InvalidArgumentException('Each stream must be readable and seekable');
            }

            $this->size += $this->map[] = $stream->getSize();
            $this->totalPart++;
            $this->streams[] = $stream;
        }

    }

    public function isSeekable()
    {
        return true;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new \RuntimeException('Stream is not writable.');
    }

    public function writeStream(ReadableStreamInterface $string)
    {
        throw new \RuntimeException('Stream is not writable.');
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getContents()
    {
        return Utils::copyToString($this);
    }

    /**
     * Attempts to seek to the given position. Only supports SEEK_SET.
     *
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->pos = $offset;
                break;
            case SEEK_CUR:
                $this->pos = $this->pos + $offset;
                break;
            case SEEK_END:
                if ($offset <= 0) {
                    $this->pos = $this->size + $offset;
                } else {
                    return false;
                }
                break;
        }

        if ($this->pos >= $this->size) {
            $this->pos = $this->size;
            $this->current = $this->totalPart - 1;

            $this->streams[$this->current]->seek(0, SEEK_END);

            return true;
        } elseif ($this->pos <= 0) {
            return $this->rewind();
        }

        $s_size = 0;
        $find = false;
        foreach ($this->map as $key => $size) {
            $sum = $size + $s_size;
            if (!$find && $this->pos <= $sum) {
                $this->current = $key;
                $this->streams[$key]->seek($size - ($sum - $this->pos), SEEK_SET);
                $find = true;
            } elseif ($find) {
                $this->streams[$key]->rewind();
            } else {
                $s_size = $sum;
            }
        }

        return true;
    }

    public function rewind()
    {
        foreach ($this->streams as $stream) {
            $stream->rewind();
        }
        $this->pos = $this->current = 0;
    }

    public function eof()
    {
        return $this->pos == $this->size;
    }

    /**
     * Reads from all of the appended streams until the length is met or EOF.
     *
     * {@inheritdoc}
     */
    public function read($length = 1024)
    {
        if ($this->streams[$this->current]->eof()) {
            if ($this->current == $this->totalPart) {
                return '';
            }
            $this->current++;
        }

        $buffer = $this->streams[$this->current]->read($length);

        $this->pos += strlen($buffer);

        return $buffer;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes each attached stream.
     *
     * {@inheritdoc}
     */
    public function close()
    {
        $this->pos = $this->current = 0;

        foreach ($this->streams as $stream) {
            $stream->close();
        }
        $this->detachMetadata();

        $this->streams = [];
    }
    
    public function detach()
    {
        $this->pos = $this->current = 0;

        foreach ($this->streams as $stream) {
            $stream->detach();
        }
        $this->detachMetadata();
        $this->streams = [];
        return null;
    }

    public function tell()
    {
        return $this->pos;
    }

    /**
     * Tries to calculate the size by adding the size of each stream.
     *
     * If any of the streams do not return a valid number, then the size of the
     * append stream cannot be determined and null is returned.
     *
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }
}
