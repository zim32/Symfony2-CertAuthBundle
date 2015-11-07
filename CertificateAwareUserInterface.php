<?php

namespace Zim\CertAuthBundle;

use Zim\CertAuthBundle\Storage\Entity\CertificateEntity;

interface CertificateAwareUserInterface
{

    /**
     * @param CertificateEntity $cert
     * @return mixed
     */
    public function setCertificate(CertificateEntity $cert);

    /**
     * @return CertificateEntity
     */
    public function getCertificate();

}