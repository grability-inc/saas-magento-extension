<?php

namespace Grability\Mobu\Helper;

use Mageplaza\Webhook\Helper\Data as ParentData;

class Data extends ParentData
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Mageplaza\Webhook\Block\Adminhtml\LiquidFilters $liquidFilters,
        \Mageplaza\Webhook\Model\HookFactory $hookFactory,
        \Mageplaza\Webhook\Model\HistoryFactory $historyFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customer,
        \GuzzleHttp\Client $guzzleClient
    ) {
        $this->guzzleClient = $guzzleClient;

        parent::__construct(
            $context, $objectManager, $storeManager, $backendUrl, $transportBuilder, $curlFactory,
            $liquidFilters, $hookFactory, $historyFactory, $customer);
    }


    /**
     * @param $headers
     * @param $authentication
     * @param $contentType
     * @param $url
     * @param $body
     * @param $method
     *
     * @return array
     */
    public function sendHttpRequest($headers, $authentication, $contentType, $url, $body, $method)
    {
        if (!$method) {
            $method = 'GET';
        }

        if ($headers && !is_array($headers)) {
            $headers = $this::jsonDecode($headers);
        }

        $headersConfig = [];

        foreach ($headers as $header) {
            $key = trim($header['name']);
            $value = trim($header['value']);
            $headersConfig[$key] = $value;
        }

        if ($authentication) {
            $headersConfig['Authorization'] = $authentication;
        }

        if ($contentType) {
            $headersConfig['Content-Type'] = $contentType;
        }

        $result = ['success' => false];

        try {
            $resultGuzzle = $this->guzzleClient->request($method, $url, [
                'headers' => $headersConfig,
                'body' => $body
            ]);

            $result['response'] = $resultGuzzle->getBody()->getContents();

            if (!empty($resultGuzzle)) {
                $result['status'] = $resultGuzzle->getStatusCode();
                if (isset($result['status']) && in_array($result['status'], [200, 201])) {
                    $result['success'] = true;
                } else {
                    $result['message'] = __('Cannot connect to server. Please try again later.');
                }
            } else {
                $result['message'] = __('Cannot connect to server. Please try again later.');
            }
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
}
