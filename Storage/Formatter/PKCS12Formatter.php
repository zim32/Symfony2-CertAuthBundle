<?php

namespace Zim\CertAuthBundle\Storage\Formatter;

use Zim\CertAuthBundle\CertificateItem;
use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateLoadingException;

class PKCS12Formatter implements CertificateFormatterInterface
{

    /**
     * @param CertificateStorageItem $result
     *
     * This method must call setBinaryContent
     */
    public function pack(CertificateStorageItem $result)
    {
        $cert = $result->getCert();
        $x509 = $cert->getX509();
        $info = openssl_x509_parse($x509);
        if (!$info) {
            $this->opensslException();
        }

        if (!openssl_pkcs12_export($x509, $pkcs12, $cert->getPrivateKey(), $result->getPassword())) {
            $this->opensslException();
        }

        $result->setBinaryContent($pkcs12);
        $result->addExtensionPiece($this->getName());
    }

    /**
     * @param CertificateStorageItem $item
     * @throws CertificateLoadingException
     *
     * This method must call setCert
     */
    public function unpack(CertificateStorageItem $item)
    {
        $data = $item->getBinaryContent();
        if (!openssl_pkcs12_read($data, $out, $item->getPassword())) {
            throw new CertificateLoadingException(openssl_error_string());
        }

        $cert = openssl_x509_read($out['cert']);
        $pkey = openssl_pkey_get_private($out['pkey']);

        $crt = new CertificateItem($cert, $pkey, null);
        $item->setCert($crt);
        $item->addExtensionPiece($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'p12';
    }

    protected function opensslException()
    {
        throw new \Exception(sprintf('OpenSSL exception: %s', openssl_error_string()));
    }
}