<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */
namespace Grability\Mobu\Model;

/**
 * Class GetManagement
 * @package Grability\Mobu\Model
 */
class GetManagement {

    private $exception;
    private $productRepository;

    public function __construct(
        \Magento\Framework\Webapi\Exception $exception,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->exception = $exception;
        $this->productRepository = $productRepository;
    }

    public function getProductConfigurations($sku)
    {
        try {
            $attributeOptions = [];

            $product = $this->productRepository->get($sku);

            if ($this->isProductConfigurable($product)) {
                    $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

                    if (!is_null($productAttributeOptions)) {
                        foreach ($productAttributeOptions as $key => $productAttribute) {
						  $attributeOptions[$key]['label'] = $productAttribute['label'];

                          foreach ($productAttribute['values'] as $attribute) {
                            $attributeOptions[$key]['values'][] = ['index' => $attribute['value_index'], 'value' => $attribute['store_label']];
                            }
                        }
                    }
            }

            return $attributeOptions;

        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }

    protected function isProductConfigurable($product)
    {
        return (!is_null($product) && method_exists($product->getTypeInstance(true), 'getConfigurableAttributesAsArray'));
    }
}
