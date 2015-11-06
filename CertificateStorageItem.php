<?php

namespace Zim\CertAuthBundle;


class CertificateStorageItem
{

    protected $cert;
    protected $identity;
    protected $extensionPieces = [];
    protected $binaryContent;
    protected $password;

    /**
     * @param mixed $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param mixed $cert
     */
    public function setCert($cert)
    {
        $this->cert = $cert;
    }

    /**
     * @return CertificateItem
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * @param mixed $binaryContent
     */
    public function setBinaryContent($binaryContent)
    {
        $this->binaryContent = $binaryContent;
    }

    /**
     * @return mixed
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    public function addExtensionPiece($val)
    {
        $this->extensionPieces[] = $val;
    }

    /**
     * @return array
     */
    public function getExtensionPieces()
    {
        return $this->extensionPieces;
    }

    /**
     * @param array $extensionPieces
     */
    public function setExtensionPieces($extensionPieces)
    {
        $this->extensionPieces = $extensionPieces;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

}