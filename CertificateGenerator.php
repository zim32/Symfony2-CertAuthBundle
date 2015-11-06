<?php

namespace Zim\CertAuthBundle;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Zim\CertAuthBundle\Event\ModifyClientCSREvent;

class CertificateGenerator
{

    protected $caPath;
    protected $caKeyPath;
    protected $caKeyPassword;
    protected $bits = 2048;
    protected $clientCSROptions = [];
    protected $eventDispatcher;

    public function __construct($caPath, $caKeyPath, TraceableEventDispatcherInterface $eventDispatcher)
    {
        $this->caPath = $caPath;
        $this->caKeyPath = $caKeyPath;
        $this->eventDispatcher = $eventDispatcher;

        if (!is_file($this->caPath) || !is_readable($this->caPath)) {
            throw new \RuntimeException('CA file does not exists or is not readable');
        }
        if (!is_file($this->caKeyPath) || !is_readable($this->caKeyPath)) {
            throw new \RuntimeException('CA key file does not exists or is not readable');
        }
    }

    protected function opensslPath($path)
    {
        return sprintf('file:/%s', $path);
    }

    protected function get($path)
    {
        return file_get_contents($path);
    }

    /**
     * @param TokenInterface $token
     * @return CertificateItem
     */
    public function generateClientCertificate(TokenInterface $token)
    {
        $privateKey = $this->generateClientPrivateKey();
        $csr = $this->generateClientCSR($token, $privateKey);
        $cert = $this->signClientCSR($csr);

        return new CertificateItem($cert, $privateKey, null);
    }

    public function generateClientPrivateKey()
    {
        $config = [
            'private_key_bits' => $this->bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);
        if (!$privateKey) {
            $this->opensslException();
        }

        return $privateKey;
    }

    public function generateClientCSR(TokenInterface $token, $privateKey)
    {
        $dn = ['commonName' => $token->getUsername()];
        $dn = array_merge($dn, $this->clientCSROptions);

        $configArgs = ['x509_extensions' => 'v3_req', 'req_extensions' => 'v3_req'];

        $event = new ModifyClientCSREvent($dn, $configArgs);
        $this->eventDispatcher->dispatch(Events::MODIFY_CSR, $event);

        $csr = openssl_csr_new($dn, $privateKey, $configArgs);
        if (!$csr) {
            $this->opensslException();
        }

        return $csr;
    }

    public function signClientCSR($csr)
    {
        $configArgs = ['x509_extensions' => 'zim_usr_cert'];

        $caPrivateKey = [$this->get($this->caKeyPath), $this->caKeyPassword];
        $cert = openssl_csr_sign($csr, $this->get($this->caPath), $caPrivateKey, 365, $configArgs, 0);
        if (!$cert) {
            $this->opensslException();
        }

        return $cert;
    }

    protected function opensslException()
    {
        throw new \Exception(sprintf('OpenSSL exception: %s', openssl_error_string()));
    }

    /**
     * @param int $bits
     */
    public function setBits($bits)
    {
        $this->bits = $bits;
    }

    /**
     * @param mixed $caKeyPassword
     */
    public function setCaKeyPassword($caKeyPassword)
    {
        $this->caKeyPassword = $caKeyPassword;
    }

    /**
     * @param array $clientCSROptions
     */
    public function setClientCSROptions($clientCSROptions)
    {
        $this->clientCSROptions = $clientCSROptions;
    }

}