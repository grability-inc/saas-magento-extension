<?php

namespace Grability\Mobu\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Grability\Mobu\Helper\IdType;
use Grability\Mobu\Helper\Config;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class ButtonSync extends Action
{
    protected $resultJsonFactory;

    /**
     * @var IdType
     */
    protected $helper;

    /**
     * @var Config
     */    
    protected $config;

    /**
     * @var State
     */    
    protected $state;

    /**
     * @var StoreManagerInterface
     */    
    protected $storeManager;

    protected $request;

    protected $storeResolver;

    protected $api_data;

    const DEV_API = [
        'url' => 'https://connect.grabilitysaas.dev/api/v2/',
        'app_id' => 'dev.grabilitysaas.mobu'
    ];

    const PRO_API = [
        'url' => 'https://connect.grabilitysaas.pro/api/v2/',
        'app_id' => 'pro.grabilitysaas.mobu'
    ];

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param IdType $helper
     * @param Config $config
     * @param State $state
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        IdType $helper,
        Config $config,
        State $state,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->config = $config;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct($context);
    }


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Grability_Mobu::config');
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->api_data = $this->getApi();

            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultJsonFactory->create();

            $syncIDType = $this->syncIdTypeMobu();

        } catch (\Exception $e) {var_dump($e->getMessage());die();
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }

        if($syncIDType == true)
            return $result->setData(['success' => true, 'message' => 'Data sincronizada con exito']);

        return $result->setData(['success' => false, 'message' => 'Data NO Sincronizada']);
    }


    public function getCurrentStoreId()
    {
        $request = $this->request;
        return (int) $request->getParam('store', 0);
    }

    public function syncIdTypeMobu()
    {
        $service = 'retailer/document-types?country={country_code}';

        //$websiteId = $this->resolveCurrentWebsiteId();var_dump($websiteId);die();
        $store_id = $this->getCurrentStoreId();

        $country_code = $this->config->getGeneralConfig('country_code', $store_id);

        $service = str_replace('{country_code}', $country_code, $service);


        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->api_data['url'] . $service,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'app_id: '. $this->api_data['app_id'],
            'x-retailer-alias: '. $this->api_data['app_id']
          ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response);

        curl_close($curl);

        $data = $response->data;

        return $this->helper->syncIdTypeMobu($data, $store_id);
    }

    /**
     * Retorna link de api segun estado de Magento
     */
    public function getApi() {

        $api = false;

        switch ( $this->state->getMode() ) {
            case \Magento\Framework\App\State::MODE_DEFAULT:
                // Action for default mode
                $api = self::DEV_API;
                break;
            case \Magento\Framework\App\State::MODE_PRODUCTION:
                // Action for production mode
                $api = self::PRO_API;
                break;
            case \Magento\Framework\App\State::MODE_DEVELOPER:
                // Action for developer mode
                $api = self::DEV_API;
                break;
        }

        return $api;
    }    

}
