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
use Trident\Component\Stream\Interfaces\ReadableStreamInterface;
use Trident\Component\Stream\Interfaces\WritableStreamInterface;
use Psr\Http\Message\StreamInterface;


class StreamFactory
{
    /**
     * Create a new stream based on the input type.
     *
     * This factory accepts the same associative array of options as described
     * in the constructor.
     *
     * @param resource|string|AbstractIOStream|StreamInterface|ReadableStreamInterface|WritableStreamInterface $resource Entity body data
     *
     * @return Stream
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function factory($resource = '')
    {
        $type = gettype($resource);

        if ($type == 'string') {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }
            return new Stream($stream);
        }

        if ($type == 'resource') {
            return new Stream($resource);
        }

        if (
            $resource instanceof StreamInterface
            ||
            $resource instanceof AbstractIOStream
            ||
            $resource instanceof ReadableStreamInterface
            ||
            $resource instanceof WritableStreamInterface
        ) {
            return $resource;
        }

        if ($type == 'object' && method_exists($resource, '__toString')) {
            return self::factory((string) $resource);
        }
/*
        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        if ($resource instanceof \Iterator) {
            return new PumpStream(function () use ($resource) {
                if (!$resource->valid()) {
                    return false;
                }
                $result = $resource->current();
                $resource->next();
                return $result;
            }, $options);
        }
*/
        throw new \InvalidArgumentException('Invalid resource type: ' . $type);
    }
}