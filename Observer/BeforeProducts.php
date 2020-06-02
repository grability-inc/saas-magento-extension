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

use Grability\Mobu\Model\Config\Source\HookType;

/**
 * Class BeforeProduct
 * @package Grability\Mobu\Observer
 */
class BeforeProducts extends AfterProducts
{
    protected $hookType = HookType::DELETE_PRODUCTS;
}
