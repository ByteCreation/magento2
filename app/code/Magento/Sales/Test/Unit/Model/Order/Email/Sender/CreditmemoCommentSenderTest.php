<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Email\Sender;

use \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;

class CreditmemoCommentSenderTest extends AbstractSenderTest
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->stepMockSetup();
        $this->stepIdentityContainerInit('\Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity');
        $this->creditmemoMock = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo',
            ['getStore', '__wakeup', 'getOrder'],
            [],
            '',
            false
        );
        $this->creditmemoMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));
        $this->creditmemoMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));
        $this->loggerMock = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );
        $this->sender = new CreditmemoCommentSender(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->senderBuilderFactoryMock,
            $this->addressRendererMock,
            $this->loggerMock
        );
    }

    public function testSendFalse()
    {
        $billingAddress = $this->addressMock;
        $this->stepAddressFormat($billingAddress);
        $result = $this->sender->send($this->creditmemoMock);
        $this->assertFalse($result);
    }

    public function testSendTrueWithCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->stepAddressFormat($billingAddress);
        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'comment' => $comment,
                        'billing' => $billingAddress,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSendWithoutSendCopy();
        $result = $this->sender->send($this->creditmemoMock, true, $comment);
        $this->assertTrue($result);
    }

    public function testSendTrueWithoutCustomerCopy()
    {
        $billingAddress = $this->addressMock;
        $comment = 'comment_test';

        $this->orderMock->expects($this->once())
            ->method('getCustomerIsGuest')
            ->will($this->returnValue(false));
        $this->stepAddressFormat($billingAddress);
        $this->identityContainerMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->templateContainerMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(
                $this->equalTo(
                    [
                        'order' => $this->orderMock,
                        'creditmemo' => $this->creditmemoMock,
                        'billing' => $billingAddress,
                        'comment' => $comment,
                        'store' => $this->storeMock,
                        'formattedShippingAddress' => 1,
                        'formattedBillingAddress' => 1
                    ]
                )
            );
        $this->stepSendWithCallSendCopyTo();
        $result = $this->sender->send($this->creditmemoMock, false, $comment);
        $this->assertTrue($result);
    }
}
