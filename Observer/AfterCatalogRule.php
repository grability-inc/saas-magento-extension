<?php

namespace Grability\Mobu\Observer;

use Exception;
use Grability\Mobu\Model\Config\Source\HookType;
use Grability\Mobu\Traits\CatalogRuleAffectedSkus;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Observer\AfterSave;
use Psr\Log\LoggerInterface;

class AfterCatalogRule extends AfterSave
{
    use CatalogRuleAffectedSkus;

    protected $hookType = HookType::NEW_CATALOG_RULE;
    protected $hookTypeUpdate = HookType::UPDATE_CATALOG_RULE;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        HookFactory $hookFactory,
        CronScheduleFactory $cronScheduleFactory,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Data $helper,
        CollectionFactory $productCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        parent::__construct($hookFactory, $cronScheduleFactory, $messageManager, $storeManager, $helper);
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        try {
            $rule = $observer->getDataObject();

            $affectedSkus = $this->getAffectedSkus($rule, $this->productCollectionFactory);

            $rule->setAffectedSkus($affectedSkus);

            if ($rule->getMpNew()) {
                parent::execute($observer);
            } else {
                $this->updateObserver($observer);
            }

        } catch (Exception $e) {
            $this->logger->error('Error in webhooks, AfterCatalogRule Observer: ' . $e->getMessage());
        }

    }
}
