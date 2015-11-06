<?php

namespace Zim\CertAuthBundle;


class CertificateItem
{
    protected $x509;
    protected $privateKey;
    protected $privateKeyPassword;

    public function __construct($x509Resource, $privateKeyResource, $privateKeyPassword)
    {
        $this->x509 = $x509Resource;
        $this->privateKey = $privateKeyResource;
        $this->privateKeyPassword = $privateKeyPassword;
    }

    /**
     * @return mixed
     */
    public function getX509()
    {
        return $this->x509;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return mixed
     */
    public function getPrivateKeyPassword()
    {
        return $this->privateKeyPassword;
    }

}