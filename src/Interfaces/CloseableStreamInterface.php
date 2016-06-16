<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream\Interfaces;

interface CloseableStreamInterface
{
    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close();
    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach();

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function __destruct();
}