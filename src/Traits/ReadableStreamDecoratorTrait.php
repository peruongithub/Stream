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


trait ReadableStreamDecoratorTrait
{
    /**
     * @var $stream ReadableStreamInterface
     */
    protected $stream;

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        $string = '';
        try {
            if (!$this->isReadable()) {
                return '';
            }

            if ($this->isSeekable()) {
                $this->seek(0);
            }

            $string = $this->getContents();
        } catch (\Exception $e) {

        }
        return $string;
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->isValidReadableStream()?$this->stream->getSize():null;
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return $this->isValidReadableStream()?$this->stream->eof():true;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @inheritdoc
     */
    public function isReadable()
    {
        return $this->isValidReadableStream()?
            $this->stream->isReadable():false;
    }

    /**
     * @inheritdoc
     */
    public function read($length = 1024)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        return $this->stream->read($length);
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        $contents = '';
        while (!$this->eof()) {
            $contents .= $this->read(1024);
        }

        return $contents;
    }

    protected function isValidReadableStream(){
        return ($this->stream instanceof ReadableStreamInterface || $this->stream instanceof StreamInterface);
    }
}