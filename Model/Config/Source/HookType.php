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
    const IMPORT_PRODUCTS = 'import_products';
    const DELETE_PRODUCTS = 'delete_products';
    const NEW_CATALOG_RULE = 'new_catalog_rule';
    const UPDATE_CATALOG_RULE = 'update_catalog_rule';
    const DELETE_CATALOG_RULE = 'delete_catalog_rule';

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
                self::IMPORT_PRODUCTS => 'Import Products',
                self::DELETE_PRODUCTS => 'Delete Products',
                self::NEW_CATALOG_RULE => 'New Catalog Rule',
                self::UPDATE_CATALOG_RULE => 'Update Catalog Rule',
                self::DELETE_CATALOG_RULE => 'Delete Catalog Rule',
            ]
        );
    }
}
