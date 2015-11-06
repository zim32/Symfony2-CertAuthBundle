<?php

namespace Zim\CertAuthBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class ModifyClientCSREvent extends Event
{
    public $dn;
    public $configArgs;

    public function __construct(&$dn, &$configArgs)
    {
        $this->dn = &$dn;
        $this->configArgs = &$configArgs;
    }
}