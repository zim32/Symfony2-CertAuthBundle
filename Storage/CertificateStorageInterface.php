<?php

namespace Zim\CertAuthBundle\Storage;

use Zim\CertAuthBundle\CertificateItem;
use Zim\CertAuthBundle\CertificateStorageItem;

interface CertificateStorageInterface
{

    /**
     * @param $identity string
     * @param $password string
     * @return CertificateStorageItem
     */
    public function load($identity, $password);

    /**
     * @param CertificateItem $item
     * @param $identity string
     * @param $password string
     * @return CertificateStorageItem
     */
    public function store(CertificateItem $item, $identity, $password);

    /**
     * @param $identity string
     * @return bool
     */
    public function has($identity);

}