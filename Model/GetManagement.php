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

    private $response = [];
    private $typesConfigurations = [];
    private $exception;
    private $productRepository;
    private $collectionFactory;
    private $configurableType;
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\Webapi\Exception $exception,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $collectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
    ) {
        $this->exception = $exception;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->configurableType = $configurableType;
    }

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

            return $this->response;

        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }

    public function getBestSellingProducts($period = 'day')
    {
        try {
            $bestSellers = $this->collectionFactory->create();
            $bestSellers->setPeriod($period)->getSelect()->order('period DESC')->order('qty_ordered DESC');

            foreach ($bestSellers as $key => $bestSeller) {
                try {
                    $bestSellerPeriod = $bestSeller->getdata('period');
                    $bestSellerSku = $this->productRepository->getById($bestSeller->getProductId())->getSku();

                    $this->response[$key]['period'] = $bestSellerPeriod;
                    $this->response[$key]['sku'] = $bestSellerSku;
                } catch(\Exception $e) {}
            }

            return $this->response;
        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }

    public function getMinimiumOrderAmount()
    {
        try {
            return $this->scopeConfig->getValue('sales/minimum_order/amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
                $this->response[$mapProcessIndex]['configurations'][strtolower($productAttribute['label'])][] = [
                    'index' => $attribute['value_index'],
                    'value' => $attribute['store_label']
                ];
            }
        }
    }

    protected function mapMultiReference($mapProcessIndex, $productsChildren)
    {
        foreach ($productsChildren as $key => $child) {
            $this->response[$mapProcessIndex]['multiReference'][$key] = [
                'id' => $child->getId(),
                'sku' => $child->getSku()
            ];

            foreach ($this->typesConfigurations as $type) {
                if ($child->getCustomAttribute($type) !== null) {
                    $this->response[$mapProcessIndex]['multiReference'][$key][$type] = $child->getCustomAttribute($type)->getValue();
                }
            }
        }
    }
    /**
     * Get Product Parent
     * @param string sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductParent($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
            $parentId = array_shift($parentIds);
            $parent = $this->productRepository->getById($parentId);

            return $parent;

        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()), 0, $this->exception::HTTP_BAD_REQUEST);
        }
    }
}
