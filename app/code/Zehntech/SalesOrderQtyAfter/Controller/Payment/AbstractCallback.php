<?php

namespace Zehntech\SalesOrderQtyAfter\Controller\Payment;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Zend\Json\Json;

class AbstractCallback extends \PensoPay\Payment\Controller\Payment\AbstractCallback
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \PensoPay\Payment\Helper\Data $pensoPayHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->_pensoPayHelper = $pensoPayHelper;
        $this->_stockRegistry = $stockRegistry;

        parent::__construct($context, $logger, $scopeConfig, $order, $orderSender, $pensoPayHelper);
    }

    public function execute()
    {
    	$body = $this->getRequest()->getContent();

    	try {
    	    $response = Json::decode($body);

    	    //Fetch private key from config and validate checksum
    	    $key = $this->_pensoPayHelper->getPrivateKey();
    	    $checksum = hash_hmac('sha256', $body, $key);
    	    $submittedChecksum = $this->getRequest()->getServer('HTTP_QUICKPAY_CHECKSUM_SHA256');

    	    if ($checksum === $submittedChecksum) {
    	        //Make sure that payment is accepted
    	        if ($response->accepted === true) {
    	            /**
    	             * Load order by incrementId
    	             * @var Order $order
    	             */
    	            $order = $this->order->loadByIncrementId($response->order_id);

    	            // inventory manage
    	            $method = $order->getPayment()->getMethod();
    	            if($method == 'pensopay'){
    	                $order->setStatus('pending_payment');
    	            }
    	            if($method != 'pensopay'){
    	                foreach ($order->getAllItems() as $item) {

    	                    $sku = $item->getProduct()->getSku();
    	                    $stockItem = $this->_stockRegistry->getStockItemBySku($sku);

    	                    if ($stockItem->getQty() >= $item->getQtyOrdered()) {

    	                        $qtyToUpdate = $stockItem->getQty() - $item->getQtyOrdered();
    	                        $stockItem->setQty($qtyToUpdate);
    	                        $this->_stockRegistry->updateStockItemBySku($sku, $stockItem);
    	                    }

    	                }
    	            }
    	            // inventory manage

    	            if (!$order->getId()) {
    	                $this->logger->debug('Failed to load order with id: ' . $response->order_id);
    	                return;
    	            }

    	            //Cancel order if testmode is disabled and this is a test payment
    	            $testMode = $this->_pensoPayHelper->getIsTestmode();

    	            if (!$testMode && $response->test_mode === true) {
    	                $this->logger->debug('Order attempted paid with a test card but testmode is disabled.');
    	                if (!$order->isCanceled()) {
    	                    $order->registerCancellation("Order attempted paid with test card")->save();
    	                }
    	                return;
    	            }

    	            //Add card metadata
    	            $payment = $order->getPayment();
    	            if (isset($response->metadata->type) && $response->metadata->type === 'card') {
    	                $payment->setCcType($response->metadata->brand);
    	                $payment->setCcLast4('xxxx-' . $response->metadata->last4);
    	                $payment->setCcExpMonth($response->metadata->exp_month);
    	                $payment->setCcExpYear($response->metadata->exp_year);

    	                $payment->setAdditionalInformation('cc_number', 'xxxx-' . $response->metadata->last4);
    	                $payment->setAdditionalInformation('exp_month', $response->metadata->exp_month);
    	                $payment->setAdditionalInformation('exp_year', $response->metadata->exp_year);
    	                $payment->setAdditionalInformation('cc_type', $response->metadata->brand);
    	            } else {
    	                if (isset($response->metadata->payment_method)) {
    	                    $payment->setCcType($response->metadata->payment_method);
    	                    $payment->setAdditionalInformation('cc_type', $response->metadata->payment_method);
    	                }
    	            }

    	            //Add transaction fee if set
    	            if ($response->fee > 0) {
    	                $this->addTransactionFee($order, $response->fee);
    	            }

    	            //Set order to processing
    	            $stateProcessing = \Magento\Sales\Model\Order::STATE_PROCESSING;

    	            if ($order->getState() !== $stateProcessing) {
    	                $order->setState($stateProcessing)
    	                    ->setStatus($order->getConfig()->getStateDefaultStatus($stateProcessing))
    	                    ->save();
    	            }

    	            //Send order email
    	            if (!$order->getEmailSent()) {
    	                $this->sendOrderConfirmation($order);
    	            }
    	        }
    	    } else {
    	        $this->logger->debug('Checksum mismatch');
    	        return;
    	    }
    	} catch (\Exception $e) {
    	    $this->logger->critical($e->getMessage());
    	}
    }

    protected function addTransactionFee(Order $order, $fee)
    {
        try {
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getSku() === \PensoPay\Payment\Helper\Data::TRANSACTION_FEE_SKU) {
                    return;
                }
            }

            /** @var \Magento\Sales\Model\Order\Item $item */
            $item = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class);
            $item->setSku(\PensoPay\Payment\Helper\Data::TRANSACTION_FEE_SKU);

            //Calculate fee price
            $feeBase = (float)$fee / 100;
            $feeTotal = $order->getStore()->getBaseCurrency()->convert($feeBase, $order->getOrderCurrencyCode());

            $name = $this->_pensoPayHelper->getTransactionFeeLabel();
            $item->setName($name);
            $item->setBaseCost($feeBase);
            $item->setBasePrice($feeBase);
            $item->setBasePriceInclTax($feeBase);
            $item->setBaseOriginalPrice($feeBase);
            $item->setBaseRowTotal($feeBase);
            $item->setBaseRowTotalInclTax($feeBase);
            $item->setCost($feeTotal);
            $item->setPrice($feeTotal);
            $item->setPriceInclTax($feeTotal);
            $item->setOriginalPrice($feeTotal);
            $item->setRowTotal($feeTotal);
            $item->setRowTotalInclTax($feeTotal);
            $item->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);
            $item->setIsVirtual(1);
            $item->setQtyOrdered(1);
            $item->setStoreId($order->getStoreId());
            $item->setOrderId($order->getId());

            $order->addItem($item);

            $order = $this->updateTotals($order, $feeBase, $feeTotal);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    protected function updateTotals($order, $feeBase, $feeTotal)
    {
        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $feeBase);
        $order->setBaseSubtotal($order->getBaseSubtotal() + $feeBase);
        $order->setGrandTotal($order->getGrandTotal() + $feeTotal);
        $order->setSubtotal($order->getSubtotal() + $feeTotal);

        return $order;
    }

    private function sendOrderConfirmation($order)
    {
        try {
            $this->orderSender->send($order);
            $order->addStatusHistoryComment(__('Order confirmation email sent to customer'))
                  ->setIsCustomerNotified(true)
                  ->save();
        } catch (\Exception $e) {
            $order->addStatusHistoryComment(__('Failed to send order confirmation email: %s', $e->getMessage()))
                  ->setIsCustomerNotified(false)
                  ->save();
        }
    }
}