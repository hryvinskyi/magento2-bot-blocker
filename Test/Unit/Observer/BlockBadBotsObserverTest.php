<?php

namespace Hryvinskyi\BotBlocker\Test\Unit\Observer;

use Hryvinskyi\BotBlocker\Model\ConfigInterface;
use Hryvinskyi\BotBlocker\Model\Validator\BotBlockerValidator;
use Hryvinskyi\BotBlocker\Observer\BlockBadBotsObserver;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;

class BlockBadBotsObserverTest extends TestCase
{
    private $configMock;
    private $botBlockerValidatorMock;
    private $blockBadBotsObserver;

    protected function setUp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '';
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->botBlockerValidatorMock = $this->createMock(BotBlockerValidator::class);
        $this->blockBadBotsObserver = new BlockBadBotsObserver($this->configMock, $this->botBlockerValidatorMock);
    }

    public function moduleDisabledDoesNotValidate()
    {
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->botBlockerValidatorMock->expects($this->never())->method('validate');

        $observerMock = $this->createMock(Observer::class);
        $this->blockBadBotsObserver->execute($observerMock);
    }

    public function moduleEnabledIpNotBlockedDoesNotThrowException()
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->botBlockerValidatorMock->method('validate')->willReturn(false);

        $observerMock = $this->createMock(Observer::class);
        $this->blockBadBotsObserver->execute($observerMock);
        $this->expectNotToPerformAssertions();
    }

    public function moduleEnabledIpBlockedThrowsException()
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->botBlockerValidatorMock->method('validate')->willReturn(true);

        $observerMock = $this->createMock(Observer::class);

        $this->expectException(\Exception::class);
        $this->blockBadBotsObserver->execute($observerMock);
    }
}