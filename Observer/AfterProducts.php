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

/**
 * Class AfterProduct
 * @package Grability\Mobu\Observer
 */
class AfterProducts extends AfterSave
{
    use SkuProcessBunch;

    protected $hookType = HookType::IMPORT_PRODUCTS;

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $bunch = $observer->getBunch();

        $item = [ 'bunch'  => $this->processBunch($bunch) ];

        $this->helper->send($item, $this->hookType);
    }
}
