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
use Trident\Component\Stream\Utils;


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
        if (!$this->isReadable()) {
            return '';
        }
        return Utils::copyToString($this->stream);
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return $this->stream->eof();
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @inheritdoc
     */
    public function isReadable()
    {
        return $this->stream->isReadable();
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
        return Utils::copyToString($this->stream);
    }
}