<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */

namespace Grability\Mobu\Observer;

use Exception;
use Grability\Mobu\Model\Config\Source\HookType;
use Grability\Mobu\Traits\SkuProcessBunch;
use Magento\Framework\Event\Observer;
use Mageplaza\Webhook\Observer\AfterSave;
use Magento\Framework\App\ObjectManager;

/**
 * Class UpdateProduct
 * @package Grability\Mobu\Observer
 */
class UpdateProduct extends AfterSave
{
    use SkuProcessBunch;

    protected $hookType = HookType::UPDATE_PRODUCT;

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $sku = [ 'sku'  => $item->getSku() ];
        //$itemSku = [ 'sku'  => $this->processBunch($items) ];

        $objectManager = ObjectManager::getInstance();
        $logger = $objectManager->create('\Psr\Log\LoggerInterface');
        $logger->info('UPDATE SKUS:' . print_r($v,true));

        $this->helper->send($sku, $this->hookType);
    }
}