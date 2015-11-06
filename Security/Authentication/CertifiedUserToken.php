<?php

namespace Zim\CertAuthBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CertifiedUserToken extends UsernamePasswordToken
{

    protected $x509;

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getX509()
    {
        return $this->x509;
    }

    /**
     * @param mixed $x509
     */
    public function setX509($x509)
    {
        $this->x509 = $x509;
    }

}