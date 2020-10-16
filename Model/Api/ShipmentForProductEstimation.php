<?php

namespace Hendel\ShippingMethodsByProduct\Model\Api;

use Magento\Framework\App\ObjectManager;

/**
 * @link https://magento.stackexchange.com/questions/247755/magento-2-how-to-display-shipping-rates-on-a-product-page
 */
class ShipmentForProductEstimation implements \Hendel\ShippingMethodsByProduct\Api\ShipmentForProductEstimationInterface
{

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $item;

    public function __construct(
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        $this->converter = $converter;
        $this->totalsCollector = $totalsCollector;
        $this->quote = $quote;
        $this->product = $product;
        $this->item = $item;
    }

    /**
     * @inheritdoc
     */
    public function estimateByProductAndAddress($sku, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        $output = [];

        $p = $this->product->get($sku);

        $this->item->setProduct($p);
        $this->item->setQty(1);

        $this->quote->addItem($this->item);

        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->addData($this->extractAddressData($address));
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($this->quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $this->quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface  $address
     * @return array
     */
    protected function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof \Magento\Quote\Api\Data\EstimateAddressInterface) {
            $className = \Magento\Quote\Api\Data\EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()->get(\Magento\Framework\Reflection\DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }
}
