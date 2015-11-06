<?php

namespace Zim\CertAuthBundle\Storage\Formatter;

use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateLoadingException;

interface CertificateFormatterInterface
{

    /**
     * @param CertificateStorageItem $result
     *
     * This method must call setBinaryContent
     */
    public function pack(CertificateStorageItem $result);

    /**
     * @param CertificateStorageItem $item
     * @throws CertificateLoadingException
     *
     * This method must call setCert
     */
    public function unpack(CertificateStorageItem $item);

    /**
     * @return string
     */
    public function getName();

}