<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */


namespace Hryvinskyi\BotBlocker\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Hryvinskyi\BotBlocker\Model\HandleStorage;
use Hryvinskyi\BotBlocker\Model\HandlerInterface;

class HandleStorageTest extends TestCase
{
    public function testGetHandlerReturnsCorrectHandler()
    {
        $handlerMock = $this->createMock(HandlerInterface::class);
        $handleStorage = new HandleStorage(['method' => $handlerMock]);

        $this->assertSame($handlerMock, $handleStorage->getHandler('method'));
    }

    public function testGetHandlerThrowsExceptionWhenHandlerNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $handleStorage = new HandleStorage([]);
        $handleStorage->getHandler('method');
    }

    public function testExecuteCallsCorrectHandlerMethod()
    {
        $handlerMock = $this->createMock(HandlerInterface::class);
        $handlerMock->expects($this->once())
            ->method('execute')
            ->with('192.168.1.1', 5, 3600)
            ->willReturn(1);

        $handleStorage = new HandleStorage(['method' => $handlerMock]);

        $this->assertEquals(1, $handleStorage->execute('method', '192.168.1.1', 5, 3600));
    }

    public function testExecuteThrowsExceptionWhenHandlerNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $handleStorage = new HandleStorage([]);
        $handleStorage->execute('method', '192.168.1.1', 5, 3600);
    }
}