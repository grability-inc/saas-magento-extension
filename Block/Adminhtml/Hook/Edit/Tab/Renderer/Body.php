<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */

namespace Grability\Mobu\Block\Adminhtml\Hook\Edit\Tab\Renderer;

use Grability\Mobu\Model\Config\Source\HookType;
use Magento\Framework\DataObject;
use Mageplaza\Webhook\Block\Adminhtml\Hook\Edit\Tab\Renderer\Body as MageplazaBody;

/**
 * Class Body
 * @package Grability\Mobu\Block\Adminhtml\Hook\Edit\Tab\Renderer
 */
class Body extends MageplazaBody
{
    public function getHookAttrCollection()
    {
        $hookType = $this->getHookType();

        if ($hookType == HookType::IMPORT_PRODUCTS || $hookType == HookType::DELETE_PRODUCTS) {
            return [
                new DataObject([
                    'name'  => 'bunch',
                    'title' => 'bunch'
                ])
            ];
        }

        return parent::getHookAttrCollection();
    }
}
