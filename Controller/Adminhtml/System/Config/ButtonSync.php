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
        $service = '/retailer/document-types?country={country_code}';

        $store_id = $this->getCurrentStoreId();

        $country_code = $this->config->getGeneralConfig('general','country_code', $store_id);

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
            'app_id: '. $this->api_data['retailer_alias'],
            'x-retailer-alias: '. $this->api_data['retailer_alias']
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
    public function getApi() 
    {
        $store_id = $this->getCurrentStoreId();

        return [
            'url' => $this->config->getGeneralConfig('api','url_api', $store_id),
            'retailer_alias' => $this->config->getGeneralConfig('api','retailer_alias', $store_id)
        ];
    }    

}
