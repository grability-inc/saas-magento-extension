<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Grability\Mobu\Model\Api;

use Zend\Code\Reflection\ClassReflection;


/**
 * Data object converter for REST
 */
class ServiceOutputProcessor  extends \Magento\Framework\Webapi\ServiceOutputProcessor
{
    public function process($data, $serviceClassName, $serviceMethodName)
    {
        $dataType = $this->methodsMapProcessor->getMethodReturnType($serviceClassName, $serviceMethodName);

        if($dataType == 'array')
            return $data;    

        if($dataType == 'stdClass')
            return json_decode(json_encode($data));

        if (class_exists($serviceClassName) || interface_exists($serviceClassName)) {
            $sourceClass = new ClassReflection($serviceClassName);
            $dataType = $this->typeProcessor->resolveFullyQualifiedClassName($sourceClass, $dataType);
        }

        return $this->convertValue($data, $dataType);
    }
}
