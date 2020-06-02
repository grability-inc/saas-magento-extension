<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */

namespace Grability\Mobu\Model\Config\Source;

use Mageplaza\Webhook\Model\Config\Source\HookType as MageplazaHookType;

/**
 * Class HookType
 * @package Grability\Mobu\Model\Config\Source
 */
class HookType extends MageplazaHookType
{
    const IMPORT_PRODUCTS   = 'import_products';
    const DELETE_PRODUCTS   = 'delete_products';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $parentToArray = parent::toArray();

        return array_merge(
            $parentToArray,
            [
                self::IMPORT_PRODUCTS   => 'Import Products',
                self::DELETE_PRODUCTS   => 'Delete Products'
            ]
        );
    }
}
