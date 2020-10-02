<?php

namespace Grability\Mobu\Traits;

trait CatalogRuleAffectedSkus
{
    public function getAffectedSkus($rule, $productCollectionFactory)
    {
        $productIds = array_keys($rule->getMatchingProductIds());

        $productCollection = $productCollectionFactory->create();

        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        $skus = [];

        foreach ($productCollection as $product) {
            $skus[] = $product->getSku();
        }

        return json_encode($skus);
    }
}