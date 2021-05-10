<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Grability\Mobu\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;

class SyncButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Grability_Mobu::system/config/buttonSync.phtml';

    protected $request;

    /**
     * @var State
     */    
    protected $state;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        State $state,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->state = $state;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('grability_mobu/system_config/buttonSync');
    }

    /**
     * @return int
     */
    public function resolveCurrentWebsiteId()
    {
        $storeId = $this->resolveCurrentStoreId();

        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        return $websiteId;
    }


    /**
     * @return int
     */
    public function resolveCurrentStoreId()
    {
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            // in admin area
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $this->request;
            $storeId = (int) $request->getParam('store', 0);
        } else {
            // frontend area
            $storeId = true; // get current store from the store resolver
        }

        return $storeId;
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'button_sync',
                'label' => __('Sync information'),
            ]
        );

        return $button->toHtml();
    }
}