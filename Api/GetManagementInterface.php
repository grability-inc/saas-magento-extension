<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */
namespace Grability\Mobu\Api;

interface GetManagementInterface {

    /**
     * Get Product Configurations
     * @param string sku
     * @return mixed
     */
     public function getProductConfigurations($sku);

     /**
     * Get Best Selling Products
     * @param string period
     * @return mixed
     */
     public function getBestSellingProducts($period);
}