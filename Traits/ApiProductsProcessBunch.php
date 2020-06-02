<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */

namespace Grability\Mobu\Traits;

use Exception;
use Magento\Framework\App\ObjectManager;

trait ApiProductsProcessBunch
{
    public function processBunch($bunch)
    {
        $objectManager = ObjectManager::getInstance();
        try {
            $skuArray = array_map(
                function ($item) {
                    return $item['sku'];
                },
                $bunch
            );

            $filterBuilder = $objectManager->create('\Magento\Framework\Api\FilterBuilder');

            $filter = $filterBuilder->setField('sku')
                ->setConditionType('in')
                ->setValue(implode(',', $skuArray))
                ->create();

            $searchCriteriaBuilder = $objectManager->create('\Magento\Framework\Api\SearchCriteriaBuilder');

            $searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteriaBuilder->setPageSize(count($bunch));

            $searchCriteria = $searchCriteriaBuilder->create();

            $serviceMethodName = 'getList';
            $serviceClassName = 'Magento\Catalog\Api\ProductRepositoryInterface';
            $service = $objectManager->get($serviceClassName);
            $serviceOutputProcessor  = $objectManager->get('\Magento\Framework\Webapi\ServiceOutputProcessor');

            $outputData = call_user_func_array([$service, $serviceMethodName], [$searchCriteria]);
            $outputData = $serviceOutputProcessor->process(
                $outputData,
                $serviceClassName,
                $serviceMethodName
            );

            return json_encode($outputData['items']);
        } catch (Exception $e) {
            $logger = $objectManager->create('\Psr\Log\LoggerInterface');
            $logger->error('Este es el debug: ' . $e->getMessage());
        }
    }
}