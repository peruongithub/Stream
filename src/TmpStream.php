<?php
/**
 * This file is part of the Trident package.
 * 
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream;

class TmpStream extends Stream
{
    public function __construct()
    {
        parent::__construct(tmpfile());
    }
} 