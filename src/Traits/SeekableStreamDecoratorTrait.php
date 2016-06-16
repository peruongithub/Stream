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

use Trident\Component\Stream\Interfaces\SeekableStreamInterface;


trait SeekableStreamDecoratorTrait
{
    /**
     * @var SeekableStreamInterface
     */
    protected $stream;

    /**
     * @inheritdoc bool
     */
    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    /**
     * @inheritdoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        return $this->stream->seek($offset, $whence);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        return $this->stream->rewind();
    }

    /**
     * @inheritdoc
     */
    public function tell()
    {
        return $this->stream->tell();
    }
}