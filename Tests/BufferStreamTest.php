<?php
namespace Trident\Component\Tests\Stream;
use Trident\Component\Stream\BufferStream;
use Trident\Component\Stream\Stream;

class BufferStreamTest extends \PHPUnit_Framework_TestCase
{
    protected function getStream($argument = null){
        return new BufferStream($argument);
    }
    /**
     * @expectedException \LogicException
     */
    public function testConstructorThrowsExceptionOnFailure()
    {
        $this->getStream(-10);
    }

    public function testConstructorDefaults()
    {
        $b = $this->getStream(null);
        $this->assertEquals(16384, $b->getMetadata('hwm'));
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testTellThrowsExceptionOnFailure()
    {
        $b = $this->getStream(10);
        $b->tell();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSeekThrowsExceptionOnFailure()
    {
        $b = $this->getStream(10);
        $b->seek(5);
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testRewindThrowsExceptionOnFailure()
    {
        $b = $this->getStream(10);
        $b->rewind();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWriteStreamThrowsExceptionOnFailure()
    {
        $b = $this->getStream(10);
        $stream = new Stream(fopen(__FILE__, 'r'));
        $b->writeStream($stream);
    }
    
    public function testHasMetadata()
    {
        $b = $this->getStream(10);
        $this->assertTrue($b->isReadable());
        $this->assertTrue($b->isWritable());
        $this->assertFalse($b->isSeekable());
        $this->assertEquals(null, $b->getMetadata('foo'));
        $this->assertEquals(10, $b->getMetadata('hwm'));
        $this->assertEquals([], $b->getMetadata());
    }

    public function testRemovesReadDataFromBuffer()
    {
        $b = $this->getStream();
        $this->assertEquals(3, $b->write('foo'));
        $this->assertEquals(3, $b->getSize());
        $this->assertFalse($b->eof());
        $this->assertEquals('foo', $b->read(10));
        $this->assertTrue($b->eof());
        $this->assertEquals('', $b->read(10));
    }

    public function testCanCastToStringOrGetContents()
    {
        $b = $this->getStream();
        $b->write('foo');
        $b->write('baz');
        $this->assertEquals('foo', $b->read(3));
        $b->write('bar');
        $this->assertEquals('bazbar', (string) $b);
        //$this->assertFalse($b->tell());
    }

    public function testDetachClearsBuffer()
    {
        $b = $this->getStream();
        $b->write('foo');
        $b->detach();
        //$this->assertEquals(0, $b->tell());
        $this->assertTrue($b->eof());
        $this->assertEquals(3, $b->write('abc'));
        $this->assertEquals('abc', $b->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers()
    {
        $b = $this->getStream(5);
        $this->assertEquals(3, $b->write('hi '));
        $this->assertEquals(2,$b->write('hello'));
        $this->assertEquals('hi he', (string) $b);
        $this->assertEquals(4, $b->write('test'));
    }
}
