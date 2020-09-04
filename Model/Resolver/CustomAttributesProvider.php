<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Grability\Mobu\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * @inheritdoc
 */
class CustomAttributesProvider implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ServiceOutputProcessor $serviceOutputProcessor
    ){
        $this->productRepository = $productRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product ProductInterface */
        $product = $value['model'];

        $attributesList = [];

        $serviceMethodName = 'get';
        $serviceClassName = 'Magento\Catalog\Api\ProductRepositoryInterface';

        $attributes = $this->productRepository->get($product->getSku());

        $attributes = $this->serviceOutputProcessor->process(
            $attributes,
            $serviceClassName,
            $serviceMethodName
        );

        $attributes = $attributes['custom_attributes'];

        foreach ($attributes as $key => $attribute){
            $value = $attribute['value'];
            $isArrayValue = is_array($value);
            $attributesList[$key]['attribute_code'] = $attribute['attribute_code'];
            $attributesList[$key]['attribute_type'] = $isArrayValue ? 'ARRAY' : 'STRING';
            $attributesList[$key]['value'] = $isArrayValue ? json_encode($value) : $value;
        }


        return $attributesList;
    }
}
