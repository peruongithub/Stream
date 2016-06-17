<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Tests\Stream;


use Trident\Component\Stream\Decorator\QuotedReadStream;
use Trident\Component\Stream\Stream;

class QuotedReadStreamTest extends DecoratedStreamTest
{
    protected function getStream($stream, $maxBytesToRead = 50){
        return new QuotedReadStream($stream, $maxBytesToRead);
    }

    public function testConvertsToString()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = $this->getStream(new Stream($handle));
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testGetsContents()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = $this->getStream(new Stream($handle));
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
    }

    public function testChecksEof()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = $this->getStream(new Stream($handle));
        $this->assertFalse($stream->eof());
        $stream->read(4);
        $this->assertTrue($stream->eof());
        $stream->close();
    }
    
    public function testGetSize()
    {
        $handle = fopen('php://temp', 'w+');
        $wrapped = new Stream($handle);
        $wrapped->write('some data some data some data some data some data ');
        $stream = $this->getStream($wrapped,20);
        $this->assertEquals(20, $stream->getSize());
        $stream->close();
    }
}