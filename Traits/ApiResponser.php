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

use Magento\Framework\App\ObjectManager;

trait ApiResponser
{
    public function successResponse($data, $code = 200)
    {
        return ['code' => $code, 'data' => $data];
    }

    public function errorResponse($message, $code = 500)
    {
        return ['code' => $code, 'message' => $message];
    }
}