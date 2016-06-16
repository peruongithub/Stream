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

/**
 * Static utility class because PHP's autoloaders don't support the concept
 * of namespaced function autoloading.
 */
class Utils
{
    const FULL = -1;

    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode Mode used to open the file
     *
     * @return resource
     * @throws \RuntimeException if the file cannot be opened
     */
    public static function open($filename, $mode)
    {
        $ex = null;
        set_error_handler(
            function () use ($filename, $mode, &$ex) {
                $ex = new \RuntimeException(
                    sprintf(
                        'Unable to open %s using mode %s: %s',
                        $filename,
                        $mode,
                        func_get_args()[1]
                    )
                );
            }
        );

        $handle = fopen($filename, $mode);
        restore_error_handler();

        if ($ex) {
            /** @var $ex \RuntimeException */
            throw $ex;
        }

        return $handle;
    }

    /**
     * Copy the contents of a stream into a string until the given number of
     * bytes have been read.
     *
     * @param ReadableStreamInterface $stream Stream to read
     * @param int $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     * @return string
     */
    public static function copyToString(ReadableStreamInterface $stream, $maxLen = -1)
    {
        $buffer = '';

        if ($maxLen === -1) {
            while (!$stream->eof()) {
                $buf = $stream->read(1048576);
                if ($buf === false) {
                    break;
                }
                $buffer .= $buf;
            }

            return $buffer;
        }

        $len = 0;
        while (!$stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            if ($buf === false) {
                break;
            }
            $buffer .= $buf;
            $len = strlen($buffer);
        }

        return $buffer;
    }

    /**
     * Copy the contents of a stream into another stream until the given number
     * of bytes have been read.
     *
     * @param ReadableStreamInterface $source Stream to read from
     * @param WritableStreamInterface $dest Stream to write to
     * @param int $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     */
    public static function copyToStream(
        ReadableStreamInterface $source,
        WritableStreamInterface $dest,
        $maxLen = self::FULL
    ) {
        if (self::FULL === $maxLen) {
            while (!$source->eof()) {
                if (!$dest->write($source->read(1024))) {
                    break;
                }
            }

            return;
        }

        $maxLen = abs((int)$maxLen);
        $bytes = 0;
        while (!$source->eof()) {
            $buf = $source->read($maxLen - $bytes);
            if (!($len = strlen($buf))) {
                break;
            }
            $bytes += $len;
            $dest->write($buf);
            if ($bytes == $maxLen) {
                break;
            }
        }
    }

    /**
     * Calculate a hash of a Stream
     *
     * @param ReadableStreamInterface $stream Stream to calculate the hash for
     * @param string $algo Hash algorithm (e.g. md5, crc32, etc)
     * @param bool $rawOutput Whether or not to use raw output
     *
     * @return string Returns the hash of the stream
     */
    public static function hash(ReadableStreamInterface $stream, $algo, $rawOutput = false) {
        $pos = 0;
        if ($stream->isSeekable()) {
            $pos = $stream->tell();
            $stream->rewind();
        }

        $ctx = hash_init($algo);
        while (!$stream->eof()) {
            hash_update($ctx, $stream->read(1048576));
        }

        $out = hash_final($ctx, (bool)$rawOutput);

        if ($stream->isSeekable()) {
            $stream->seek($pos);
        }

        return $out;
    }

    /**
     * Read a line from the stream up to the maximum allowed buffer length
     *
     * @param ReadableStreamInterface $stream Stream to read from
     * @param int $maxLength Maximum buffer length
     * @param string $eol Line ending
     *
     * @return string|bool
     */
    public static function readLine(ReadableStreamInterface $stream, $maxLength = null, $eol = PHP_EOL)
    {
        $buffer = '';
        $size = 0;
        $negEolLen = -strlen($eol);

        while (!$stream->eof()) {
            if (false === ($byte = $stream->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            // Break when a new line is found or the max length - 1 is reached
            if (++$size == $maxLength || substr($buffer, $negEolLen) === $eol) {
                break;
            }
        }

        return $buffer;
    }
}
