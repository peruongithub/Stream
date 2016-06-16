<?php
namespace Trident\Component\Tests\Stream;
use Trident\Component\Stream\Stream;

/**
 * @covers Trident\Component\Stream\Stream
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument()
    {
        new Stream(true);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteStreamThrowsExceptionOnFailure()
    {
        $stream1 = new Stream(fopen(__FILE__, 'r'));
        $stream2 = new Stream(fopen(__FILE__, 'r'));
        $stream1->writeStream($stream2);
    }

    public function testWriteStream()
    {
        $stream1 = new Stream(fopen('php://temp', 'r+'));
        $stream2 = new Stream(fopen(__FILE__, 'r'));
        $stream1->writeStream($stream2);
        $this->assertSame($stream2->getContents(), $stream1->getContents());
    }

    public function testConstructorInitializesProperties()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertInternalType('array', $stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());
        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testConvertsToString()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testGetsContents()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
    }

    public function testChecksEof()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        $this->assertFalse($stream->eof());
        $stream->read(4);
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    public function testGetSize()
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');
        $stream = new Stream($handle);
        $this->assertEquals($size, $stream->getSize());
        // Load from cache
        $this->assertEquals($size, $stream->getSize());
        $stream->close();
    }

    public function testEnsuresSizeIsConsistent()
    {
        $h = fopen('php://temp', 'w+');
        $this->assertEquals(3, fwrite($h, 'foo'));
        $stream = new Stream($h);
        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(4, $stream->write('test'));
        $this->assertEquals(7, $stream->getSize());
        $this->assertEquals(7, $stream->getSize());
        $stream->close();
    }

    public function testProvidesStreamPosition()
    {
        $handle = fopen('php://temp', 'w+');
        $stream = new Stream($handle);
        $this->assertEquals(0, $stream->tell());
        $stream->write('foo');
        $this->assertEquals(3, $stream->tell());
        $stream->seek(1);
        $this->assertEquals(1, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());
        $stream->close();
    }

    public function testCanDetachStream()
    {
        $r = fopen('php://temp', 'w+');
        $stream = new Stream($r);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($r, $stream->detach());
        $stream->detach();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $throws = function (callable $fn) use ($stream) {
            try {
                $fn($stream);
                $this->fail();
            } catch (\Exception $e) {}
        };
        $throws(function ($stream) { $stream->read(10); });
        $throws(function ($stream) { $stream->write('bar'); });
        $throws(function ($stream) { $stream->seek(10); });
        $throws(function ($stream) { $stream->tell(); });
        $throws(function ($stream) { $stream->eof(); });
        $throws(function ($stream) { $stream->getSize(); });
        $throws(function ($stream) { $stream->getContents(); });
        $this->assertSame('', (string) $stream);
        $stream->close();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteThrowsExceptionOnFailure()
    {
        $stream = new Stream(fopen(__FILE__, 'r'));
        $stream->write('bar');
    }

    public function testCloseClearProperties()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream($handle);
        $stream->close();

        $this->assertEmpty($stream->getMetadata());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
    }
}
