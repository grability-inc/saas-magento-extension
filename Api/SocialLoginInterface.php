<?php

namespace Grability\Mobu\Api;

interface SocialLoginInterface
{
    /**
     * login
     * @param string $email_address
     * @return stdClass
     */
    public function login($email_address);
}
