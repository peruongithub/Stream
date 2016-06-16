<?php
namespace Trident\Component\Tests\Stream;
use Trident\Component\Stream\Stream;
use Trident\Component\Stream\StreamFactory;

/**
 * @covers Trident\Component\Stream\Stream
 */
class StreamFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testKeepsPositionOfResource()
    {
        $h = fopen(__FILE__, 'r');
        fseek($h, 10);
        $stream = StreamFactory::factory($h);
        $this->assertEquals(10, $stream->tell());
        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = StreamFactory::factory('foo');
        $this->assertInstanceOf('Trident\Component\Stream\Stream', $stream);
        $this->assertEquals('foo', $stream->getContents());
        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $s = StreamFactory::factory();
        $this->assertInstanceOf('Trident\Component\Stream\Stream', $s);
    }

    public function testFactoryCreatesFromResource()
    {
        $r = fopen(__FILE__, 'r');
        $s = StreamFactory::factory($r);
        $this->assertInstanceOf('Trident\Component\Stream\Stream', $s);
        $this->assertSame(file_get_contents(__FILE__), (string) $s);
    }

    public function testFactoryCreatesFromObjectWithToString()
    {
        $r = new HasToString();
        $s = StreamFactory::factory($r);
        $this->assertInstanceOf('Trident\Component\Stream\Stream', $s);
        $this->assertEquals('foo', (string) $s);
    }

    public function testCreatePassesThrough()
    {
        $s = StreamFactory::factory('foo');
        $this->assertSame($s, StreamFactory::factory($s));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionForUnknown()
    {
        StreamFactory::factory(new \stdClass());
    }
}

class HasToString
{
    public function __toString() {
        return 'foo';
    }
}
