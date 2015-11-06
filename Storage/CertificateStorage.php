<?php

namespace Zim\CertAuthBundle\Storage;

use Zim\CertAuthBundle\CertificateItem;
use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;
use Zim\CertAuthBundle\Storage\Filter\AbstractCertificateFilter;
use Zim\CertAuthBundle\Storage\Formatter\CertificateFormatterInterface;
use Zim\CertAuthBundle\Storage\Persister\CertificatePersisterInterface;

class CertificateStorage implements CertificateStorageInterface
{

    /**
     * @var CertificateFormatterInterface
     */
    protected $formatter;

    /**
     * @var CertificatePersisterInterface
     */
    protected $persister;

    /**
     * @var AbstractCertificateFilter
     */
    protected $filters;


    public function __construct(CertificateFormatterInterface $formatter, CertificatePersisterInterface $persister)
    {
        $this->formatter = $formatter;
        $this->persister = $persister;
    }

    /**
     * @param string $identity
     * @param $password string
     * @return CertificateStorageItem
     */
    public function load($identity, $password)
    {
        $result = new CertificateStorageItem();
        $result->setIdentity($identity);
        $result->setPassword($password);
        $this->persister->load($result);
        $this->formatter->unpack($result);

        $result->setExtensionPieces(array_reverse($result->getExtensionPieces()));

        return $result;
    }

    /**
     * @param $identity string
     * @return bool
     */
    public function has($identity)
    {
        $result = new CertificateStorageItem();
        $result->setIdentity($identity);
        try {
            $this->persister->load($result);
        } catch (CertificateNotFoundException $e) {
            return false;
        }

        return !empty($result->getBinaryContent());
    }

    /**
     * @param CertificateItem $item
     * @param string $identity
     * @param $password string
     * @return CertificateStorageItem
     */
    public function store(CertificateItem $item, $identity, $password)
    {
        $result = new CertificateStorageItem();
        $result->setIdentity($identity);
        $result->setPassword($password);
        $result->setCert($item);
        $this->formatter->pack($result);
        $this->persister->store($result);

        return $result;
    }

}