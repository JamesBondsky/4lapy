<?php

namespace LinguaLeo\ExpertSender\Chunks;

use PHPUnit\Framework\TestCase;

class HeaderChunkTest extends TestCase
{
    public function testGetText()
    {
        /**
         * @var SimpleChunk|\PHPUnit_Framework_MockObject_MockObject
         */
        $bodyChunk = $this
            ->getMockBuilder(SimpleChunk::class)
            ->setMethods(['getText'])
            ->disableOriginalConstructor()
            ->getMock();
        $bodyChunk->expects($this->once())->method('getText')->will($this->returnValue('body'));

        $headerChunk = new HeaderChunk('api-key', $bodyChunk);
        $text = $headerChunk->getText();

        $this->assertRegExp('~body~', $text);
        $this->assertRegExp('~api-key~', $text);
    }
}
