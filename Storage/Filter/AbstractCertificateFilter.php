<?php

namespace Zim\CertAuthBundle\Storage\Filter;

use Zim\CertAuthBundle\CertificateStorageItem;

abstract class AbstractCertificateFilter
{

    /**
     * @param CertificateStorageItem $item
     *
     * This method must modify setBinaryContent
     */
    abstract public function forward(CertificateStorageItem $item);

    /**
     * @param CertificateStorageItem $item
     * This method must call setBinaryContent
     */
    abstract public function reverse(CertificateStorageItem $item);

}