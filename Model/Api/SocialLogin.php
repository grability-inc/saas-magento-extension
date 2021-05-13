<?php

namespace Grability\Mobu\Model\Api;

use Psr\Log\LoggerInterface;
use \Magento\Integration\Model\Oauth\TokenFactory;
use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Session;
use Grability\Mobu\Helper\Config;
use Grability\Mobu\Traits\ApiResponser;

class SocialLogin
{
    use ApiResponser;

    protected $logger;
    private $tokenModelFactory;
    private $customer;
    private $customerSession;
    private $config;
    
    public function __construct(
        LoggerInterface $logger,
        TokenFactory $tokenFactory,
        Customer $customer,
        Session $session,
        Config $config
    )
    {
        $this->logger = $logger;
        $this->tokenModelFactory = $tokenFactory;
        $this->customer = $customer;
        $this->customerSession = $session;
        $this->config = $config;
    }
    
    public function login($email_address)
    {
        if(!$this->resolveHeaders())
            return $this->errorResponse('impossible to process request',402);

        if (!isset($email_address) || $email_address == "")
            return $this->errorResponse('email is not defined',402);

        $this->customer->setWebsiteId(1);
        $this->customer->loadByEmail($email_address);

        if($this->customer->getId()){
            $this->customerSession->setCustomerAsLoggedIn($this->customer); // Load customer session
            $customerToken = $this->tokenModelFactory->create();
            $tokenKey = $customerToken->createCustomerToken($this->customer->getId())->getToken();
            return $this->successResponse(['token' => $tokenKey]);
        }else{
            return $this->errorResponse('customer is not resolved',402);
        }
    }

    public function resolveHeaders()
    {
        if(!isset(getallheaders()['X-Grability-Key']) || getallheaders()['X-Grability-Key'] == ''){
            return false;
        }
        else{
            if(getallheaders()['X-Grability-Key'] != $this->config->getGeneralConfig('auth','secret_key')){
                return false;
            }else{
                return true;
            }
        }
    }
}
