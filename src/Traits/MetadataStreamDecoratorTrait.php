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
use Trident\Component\Stream\Interfaces\MetadataStreamInterface;
use Trident\Component\Stream\Interfaces\SeekableStreamInterface;

trait MetadataStreamDecoratorTrait
{
    /**
     * @var $stream MetadataStreamInterface
     */
    protected $stream;

    public function isSeekable()
    {
        if(
            $this->stream instanceof StreamInterface ||
            $this->stream instanceof SeekableStreamInterface ||
            $this->stream instanceof MetadataStreamInterface
        ){
            return $this->stream->isSeekable();
        }
        return false;
    }

    public function isBlocked()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->isBlocked();
        }elseif ($this->stream instanceof StreamInterface){
            return true === $this->stream->getMetadata('blocked');
        }
        return false;
    }

    public function isTimeOut()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->isTimeOut();
        }elseif ($this->stream instanceof StreamInterface){
            return true === $this->stream->getMetadata('timed_out');
        }
        return false;
    }

    public function getUri()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->getUri();
        }elseif ($this->stream instanceof StreamInterface){
            return $this->stream->getMetadata('uri');
        }
        return '';
    }

    public function getType()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->getType();
        }elseif ($this->stream instanceof StreamInterface){
            return $this->stream->getMetadata('stream_type');
        }
        return '';
    }

    public function getWrapperType()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->getWrapperType();
        }elseif ($this->stream instanceof StreamInterface){
            return $this->stream->getMetadata('wrapper_type');
        }
        return '';
    }

    public function getWrapperData()
    {
        if($this->stream instanceof MetadataStreamInterface){
            return $this->stream->getWrapperData();
        }elseif ($this->stream instanceof StreamInterface){
            return $this->stream->getMetadata('wrapper_data');
        }
        return null;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $this->stream->getMetadata($key);
    }
}