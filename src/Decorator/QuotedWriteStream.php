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


class QuotedWriteStream implements StreamInterface, ReadableStreamInterface, WritableStreamInterface, MetadataStreamInterface
{
    use MetadataStreamDecoratorTrait;
    use SeekableStreamDecoratorTrait;
    use ReadableStreamDecoratorTrait;
    use WritableStreamDecoratorTrait;

    private $maxBytesToWrite;
    private $bytesWritten = 0;

    /**
     * @param WritableStreamInterface $stream
     * @param int $maxBytesToWrite
     */
    public function __construct(WritableStreamInterface $stream, $maxBytesToWrite)
    {
        $this->stream = $stream;
        $this->maxBytesToWrite = abs((int)$maxBytesToWrite);
    }

    public function detach()
    {
        $this->maxBytesToWrite = 0;

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
        $this->maxBytesToWrite = 0;
        $this->stream->close();
    }

    public function write($string)
    {
        $length = strlen($string);
        $dif = $this->maxBytesToWrite - $this->bytesWritten;

        if (0 == $dif) {
            $result = 0;
        }elseif ($length <= $dif) {
            $result = $this->stream->write($string);
        }else{
            $result = $this->stream->write(substr($string, 0, $dif));
        }

        $this->bytesWritten += $result;

        return $result;
    }

    public function getTotalWrittenBytes(){
        return (int)$this->bytesWritten;
    }
}
