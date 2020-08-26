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

    private $attributeOptions = [];
    private $typesConfigurations = [];
    private $exception;
    private $productRepository;

    public function __construct(
        \Magento\Framework\Webapi\Exception $exception,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->exception = $exception;
        $this->productRepository = $productRepository;    }

    public function getProductConfigurations($sku)
    {
        try {
            $product = $this->productRepository->get($sku);

            if ($this->isProductConfigurable($product)) {
                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

                if (!is_null($productAttributeOptions)) {
                    $this->mapConfigurations(0, $productAttributeOptions);
                }

                $productsChildren = $product->getTypeInstance()->getUsedProducts($product);

                if (!is_null($productsChildren)) {
                    $this->mapMultiReference(1, $productsChildren);
                }
            }

            return $this->attributeOptions;

        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }

    protected function isProductConfigurable($product)
    {
        return (!is_null($product) && method_exists($product->getTypeInstance(true), 'getConfigurableAttributesAsArray'));
    }

    protected function mapConfigurations($mapProcessIndex, $productAttributeOptions)
    {
        foreach ($productAttributeOptions as $key => $productAttribute) {

            $this->typesConfigurations[] = strtolower($productAttribute['label']);

            foreach ($productAttribute['values'] as $attribute) {
                $this->attributeOptions[$mapProcessIndex]['configurations'][strtolower($productAttribute['label'])][] = [
                    'index' => $attribute['value_index'],
                    'value' => $attribute['store_label']
                ];
            }
        }
    }

    protected function mapMultiReference($mapProcessIndex, $productsChildren)
    {
        foreach ($productsChildren as $key => $child) {
            $this->attributeOptions[$mapProcessIndex]['multiReference'][$key] = [
                'id' => $child->getId(),
                'sku' => $child->getSku()
            ];

            foreach ($this->typesConfigurations as $type) {
                if ($child->getCustomAttribute($type) !== null) {
                    $this->attributeOptions[$mapProcessIndex]['multiReference'][$key][$type] = $child->getCustomAttribute($type)->getValue();
                }
            }
        }
    }
}
