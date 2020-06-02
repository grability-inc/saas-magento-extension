<?php
/**
 * Grability
 *
 * @category            Grability
 * @package             Grability_Mobu
 * @copyright           Copyright (c) Grability (https://www.grability.com/)
 * @termsAndConditions  https://www.grability.com/legal
 */
namespace Grability\Mobu\Model;

/**
 * Class PostManagement
 * @package Grability\Mobu\Model
 */
class PostManagement {

    private $dataAddressFactory;
    private $addressRepository;
    private $exception;

    public function __construct(
        \Magento\Customer\Api\Data\AddressInterfaceFactory $dataAddressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Webapi\Exception $exception
    ) {
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->exception = $exception;
    }

    public function createAddress($customerId, $address)
    {
        try {
            $response = '';

            if (!empty($customerId) && isset($customerId) && $customerId != "") {
                $infoAddress = $this->parseAddress($customerId, $address);
                $response = $this->addressRepository->save($infoAddress)->getId();
            } else {
                throw new $this->exception(__('customerId required'),0,$this->exception::HTTP_BAD_REQUEST);
            }

            return $response;
        } catch(\Exception $e) {
            throw new $this->exception(__($e->getMessage()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }

    protected function parseAddress($customerId, $infoAddress)
    {
        try {
            $address = $this->dataAddressFactory->create();

            $address->setFirstname($infoAddress['firstname']);
            $address->setLastname($infoAddress['lastname']);
            $address->setTelephone($infoAddress['telephone']);
            $address->setStreet($infoAddress['street']);
            $address->setCity($infoAddress['city']);
            $address->setCountryId($infoAddress['country_id']);
            $address->setPostcode($infoAddress['postcode']);
            $address->setRegionId($infoAddress['region']['region_id']);
            $address->setIsDefaultShipping($infoAddress['default_shipping']);
            $address->setIsDefaultBilling($infoAddress['default_billing']);
            $address->setCustomerId($customerId);

            return $address;
        } catch (\Exception $e) {
            throw new $this->exception(__($e->getMessange()),0,$this->exception::HTTP_BAD_REQUEST);
        }
    }
}