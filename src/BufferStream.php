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

use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Psr\Http\Message\StreamInterface;


class BufferStream implements   ReadableStreamInterface,
                                WritableStreamInterface,
                                StreamInterface
{
    protected $buffer = '';
    
    private $hwm;

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     * @throws \LogicException
     */
    public function __construct($hwm = 16384)
    {
        $hwm = null === $hwm?16384:$hwm;
        $hwm = (int) $hwm;
        if(0 >= $hwm){
            throw new \LogicException(sprintf('High water mark must bee greater than zero. "%d" given',$hwm));
        }
        $this->hwm = $hwm;
    }

    public function __destruct()
    {
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getContents()
    {
        $buffer = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }

    public function close()
    {
        $this->buffer = '';
    }

    public function detach()
    {
        $this->buffer = '';
        return null;
    }

    public function getSize()
    {
        return strlen($this->buffer);
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function isSeekable()
    {
        return false;
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a BufferStream');
    }

    public function eof()
    {
        return strlen($this->buffer) === 0;
    }

    public function tell()
    {
        throw new \RuntimeException('Cannot determine the position of a BufferStream');
    }

    /**
     * @inheritdoc
     */
    public function read($length = 1024)
    {
        $currentLength = strlen($this->buffer);
        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);
        }

        return $result;
    }

    public function writeStream(ReadableStreamInterface $stream)
    {
        throw new \RuntimeException(
            'Cannot writing to BufferStream from another stream. Please use BufferStream::write() method.'
        );
    }

    /**
     * @inheritdoc
     */
    public function write($string)
    {
        $length = strlen($string);
        $buffered = strlen($this->buffer);
        $friSpace = $this->hwm - $buffered;
        if (0 == $friSpace) {
            return 0;
        } elseif ($length <= $friSpace) {
            $this->buffer .= $string;

            return $length;
        }
        //else
        $string = substr($string, 0, $friSpace);
        $this->buffer .= $string;

        return $friSpace;
    }

    /*
     * hard mode may corrupt the data
     *
        public function write($string)
        {
            $friSpace = $this->hwm - strlen($this->buffer);
            if(0 >= $friSpace){
                return 0;
            }
            $this->buffer .= $string;

            return strlen($string);
        }
    */
    public function getMetadata($key = null)
    {
        if ($key == 'hwm') {
            return $this->hwm;
        }

        return $key ? null : [];
    }
}