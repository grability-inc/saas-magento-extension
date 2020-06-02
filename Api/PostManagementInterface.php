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

interface PostManagementInterface {

    /**
     * Create Address
     * @param string $customerId
     * @param mixed $address
     * @return mixed
     */
     public function createAddress($customerId, $address);
}