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


use Trident\Component\Stream\Interfaces\MetadataStreamInterface;

trait MetadataControlStreamDecoratorTrait
{
    /**
     * @var $stream MetadataStreamInterface
     */
    protected $stream;

    /**
     * @param array $metadata
     */
    protected function setMetadata(array $metadata)
    {
        $this->stream->setMetadata($metadata);
    }

    protected function detachMetadata(){
        $this->stream->detachMetadata();
    }

    public function updateMetadata(){
        $this->stream->updateMetadata();
    }
}