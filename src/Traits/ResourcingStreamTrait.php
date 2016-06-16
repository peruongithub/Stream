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


trait ResourcingStreamTrait
{
    /**
     * @var resource $resource
     */
    protected $resource;

    /**
     * @inheritdoc
     */
    public function isLocal()
    {
        return is_resource($this->resource) && stream_is_local($this->resource);
    }

    /**
     * @inheritdoc
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @inheritdoc
     */
    public function stat()
    {
        return $this->isLocal()? fstat($this->resource) : [];
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
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
        $this->detach();
    }

    protected function detachResource()
    {
        $result = $this->resource;
        $this->resource = null;
        return $result;
    }
}