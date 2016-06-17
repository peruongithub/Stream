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

use Trident\Component\Stream\Interfaces\ReadableStreamInterface;


class QuotedReadStream extends DecoratedStream
{
    protected $maxBytesToRead;
    protected $bytesRead = 0;

    /**
     * @var ReadableStreamInterface $stream
     */
    protected $stream;

    /**
     * @param ReadableStreamInterface $stream
     * @param int $maxBytesToRead
     */
    public function __construct(ReadableStreamInterface $stream, $maxBytesToRead)
    {
        parent::__construct($stream);
        $this->maxBytesToRead = abs((int)$maxBytesToRead);
    }

    public function detach()
    {
        $this->maxBytesToRead = 0;

        return parent::detach();
    }

    public function close()
    {
        $this->maxBytesToRead = 0;
        parent::close();
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
        if(!$this->isValidReadableStream()){
            return null;
        }
        $realSize = $this->stream->getSize();
        if(null !== $realSize && $this->maxBytesToRead > $realSize){
            return $realSize;
        }
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
