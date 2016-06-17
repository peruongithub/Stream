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
use Trident\Component\Stream\Interfaces\CloseableStreamInterface;
use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Trident\Component\Stream\Traits\MetadataStreamDecoratorTrait;
use Trident\Component\Stream\Traits\ReadableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\SeekableStreamDecoratorTrait;
use Trident\Component\Stream\Traits\WritableStreamDecoratorTrait;

class DecoratedStream implements ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface, StreamInterface
{
    use MetadataStreamDecoratorTrait{
        MetadataStreamDecoratorTrait::isSeekable insteadof SeekableStreamDecoratorTrait;
    }
    use SeekableStreamDecoratorTrait;
    use ReadableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;

    /**
     * @var CloseableStreamInterface
     */
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function detach()
    {
        $return = null;
        if(is_object($this->stream) && ($this->stream instanceof CloseableStreamInterface || $this->stream instanceof StreamInterface)){
            $return = $this->stream->detach();
            $this->stream = null;
        }

        return $return;
    }

    /**
     * Closes the resource when the destructed
     */
    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if(is_object($this->stream) && ($this->stream instanceof CloseableStreamInterface || $this->stream instanceof StreamInterface)){
            $this->stream->close();
            $this->stream = null;
        }
    }

}