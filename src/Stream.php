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

use Psr\Http\Message\StreamInterface;
use Trident\Component\Stream\Exception\StreamTimeoutException;
use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\ResourcingStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Trident\Component\Stream\Traits\MetadataStreamTrait;
use Trident\Component\Stream\Traits\ResourcingStreamTrait;
use Trident\Component\Stream\Traits\StreamModeTrait;

/**
 * PHP resource implementation
 */
class Stream implements ReadableStreamInterface,
                        WritableStreamInterface,
                        MetadataStreamInterface,
                        ResourcingStreamInterface,
                        StreamInterface
{
    use StreamModeTrait;
    use MetadataStreamTrait;
    use ResourcingStreamTrait;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new \InvalidArgumentException('First parameter must be a valid stream resource');
        }
        $this->resource = $resource;
        $this->setMetadata(stream_get_meta_data($resource));
        $this->setMode($this->metadata['mode']);
        //stream_set_blocking($this->resource, 0);
    }

    public function detach()
    {
        $this->detachMode();
        $this->detachMetadata();

        return $this->detachResource();
    }

    public function __toString()
    {
        if (!$this->resource) {
            return '';
        }

        try {
            $this->seek(0);

            return (string)stream_get_contents($this->resource);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        } elseif (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException(
                sprintf('Unable to seek to stream position %d with whence %s', $offset, var_export($whence, true))
            );
        }
    }

    public function getContents()
    {
        if (!is_resource($this->resource)) {
            return '';
        }

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function getSize()
    {
        if (!$this->isLocal()) {
            return null;
        }

        // Clear the stat cache if the resource has a URI
        $uri = $this->getUri();
        if ($uri) {
            clearstatcache(true, $uri);
        }

        $stats = $this->stat();

        return isset($stats['size']) ? $stats['size'] : null;
    }

    public function eof()
    {
        return !is_resource($this->resource) || ($this->metadata['eof'] = feof($this->resource));
    }

    public function tell()
    {
        $result = false;
        if ($this->isLocal() && $this->impliesPositioningCursorAtTheBeginning()) {
            $result = ftell($this->resource);
        }

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function updateMetadata()
    {
        if (is_resource($this->resource)) {
            $this->setMetadata(stream_get_meta_data($this->resource));
        }
    }

    public function read($length = 1024)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        if (!$this->isLocal()) {
            $this->updateMetadata();
            if ($this->getMetadata('timed_out')) {
                throw new StreamTimeoutException('Unable read a stream.');
            }
        }

        $result = fread($this->resource, $length);

        if (false === $result) {
            throw new \RuntimeException('Unable read a stream');
        }

        return $result;
    }

    public function writeStream(ReadableStreamInterface $stream)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }
        $written = 0;
        while (!$stream->eof()) {
            $write = fwrite($this->resource, $stream->read(1024), 1024);
            if ($write === false) {
                return $written;
            }
            $written += $write;
        }

        return $written;
    }

    /**
     * @param string $string
     * @return int
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable.');
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }
}
