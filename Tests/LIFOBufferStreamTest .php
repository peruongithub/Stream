<?php
namespace Trident\Component\Tests\Stream;
use Trident\Component\Stream\LIFOBufferStream;

class LIFOBufferStreamTest extends BufferStreamTest
{
    protected function getStream($argument = null){
        return new LIFOBufferStream($argument);
    }

    public function testRemovesReadDataFromBuffer()
    {
        $b = $this->getStream();
        $this->assertEquals(3, $b->write('foo'));
        $this->assertEquals(3, $b->getSize());
        $this->assertFalse($b->eof());
        $this->assertEquals('oof', $b->read(10));
        $this->assertTrue($b->eof());
        $this->assertEquals('', $b->read(10));
    }

    public function testCanCastToStringOrGetContents()
    {
        $b = $this->getStream();
        $b->write('foo');
        $b->write('baz');
        $this->assertEquals('zab', $b->read(3));
        $b->write('bar');
        $this->assertEquals('raboof', (string) $b);
    }

    public function testDetachClearsBuffer()
    {
        $b = $this->getStream();
        $b->write('foo');
        $b->detach();
        $this->assertTrue($b->eof());
        $this->assertEquals(3, $b->write('abc'));
        $this->assertEquals('cab', $b->read(10));
    }

    public function testExceedingHighwaterMarkReturnsFalseButStillBuffers()
    {
        $b = $this->getStream(5);
        $this->assertEquals(3, $b->write('hi '));
        $this->assertEquals(2,$b->write('hello'));
        $this->assertEquals('eh ih', (string) $b);
        $this->assertEquals(4, $b->write('test'));
    }
}
