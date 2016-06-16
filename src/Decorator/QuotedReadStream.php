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


class QuotedReadStream implements StreamInterface, ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface
{
    use MetadataStreamDecoratorTrait;
    use SeekableStreamDecoratorTrait;
    use ReadableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;

    private $maxBytesToRead;
    private $bytesRead = 0;

    /**
     * @param ReadableStreamInterface $stream
     * @param int $maxBytesToRead
     */
    public function __construct(ReadableStreamInterface $stream, $maxBytesToRead)
    {
        $this->stream = $stream;
        $this->maxBytesToRead = abs((int)$maxBytesToRead);
    }

    public function detach()
    {
        $this->maxBytesToRead = 0;

        return $this->stream->detach();
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
        $this->maxBytesToRead = 0;
        $this->stream->close();
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        if(0 == $this->maxBytesToRead || 0 == ($this->maxBytesToRead - $this->bytesRead)){
            return true;
        }
        return $this->stream->eof();
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->maxBytesToRead;
    }

    public function read($length = 1024)
    {
        $dif = $this->maxBytesToRead - $this->bytesRead;

        if (0 == $dif) {
            $result = '';
        }elseif ($length <= $dif) {
            $result = $this->stream->read($length);
        }else{
            $result = $this->stream->read((int)$dif);
        }
        $this->bytesRead += strlen($result);

        return $result;
    }

    public function getTotalReadBytes(){
        return (int)$this->bytesRead;
    }
}
