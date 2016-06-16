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


trait MetadataStreamTrait
{
    protected $metadata = [];

    public function isSeekable()
    {
        return (isset($this->metadata['seekable']) && true === $this->metadata['seekable']);
    }

    public function isBlocked()
    {
        return (isset($this->metadata['blocked']) && true === $this->metadata['blocked']);
    }

    public function isTimeOut()
    {
        return (isset($this->metadata['timed_out']) && true === $this->metadata['timed_out']);
    }

    public function getUri()
    {
        return isset($this->metadata['uri']) ? $this->metadata['uri'] : '';
    }

    public function getType()
    {
        return isset($this->metadata['stream_type']) ? $this->metadata['stream_type'] : '';
    }

    public function getWrapperType()
    {
        return isset($this->metadata['wrapper_type']) ? $this->metadata['wrapper_type'] : '';
    }

    public function getWrapperData()
    {
        return isset($this->metadata['wrapper_data']) ? $this->metadata['wrapper_data'] : null;
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
        if (!$key) {
            return $this->metadata;
        } elseif (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        } else {
            return null;
        }
    }

    /**
     * @param array $metadata
     */
    protected function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    protected function detachMetadata(){
        $this->metadata = [];
    }

    public function updateMetadata(){
        
    }
}