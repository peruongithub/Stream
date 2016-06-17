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

use Trident\Component\Stream\Interfaces\WritableStreamInterface;


class QuotedWriteStream extends DecoratedStream
{
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

        return parent::detach();
    }

    public function close()
    {
        $this->maxBytesToWrite = 0;
        parent::close();
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
