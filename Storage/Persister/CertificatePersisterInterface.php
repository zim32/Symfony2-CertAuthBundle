<?php

namespace Zim\CertAuthBundle\Storage\Persister;


use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;

interface CertificatePersisterInterface
{

    /**
     * @param CertificateStorageItem $item
     *
     */
    public function store(CertificateStorageItem $item);

    /**
     * @param CertificateStorageItem $item
     * @throws CertificateNotFoundException
     * This method must call setBinaryContent
     *
     */
    public function load(CertificateStorageItem $item);


}