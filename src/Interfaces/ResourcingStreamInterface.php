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

interface ResourcingStreamInterface
{
    /**
     * @return resource
     */
    public function getResource();

    /**
     * Gets information about a file using an open file pointer
     * @see http://php.net/manual/en/function.fstat.php
     * @return array
     */
    public function stat();

    /**
     * Checks if a stream is a local stream
     * @return bool
     */
    public function isLocal();
}
