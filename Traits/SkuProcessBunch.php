<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */

namespace Grability\Mobu\Traits;

trait SkuProcessBunch
{
    public function processBunch($bunch)
    {
        $skuArray = array_map(
            function ($item) {
                return $item['sku'];
            },
            $bunch
        );

        return  json_encode($skuArray);
    }
}
