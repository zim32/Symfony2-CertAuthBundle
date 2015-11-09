<?php

namespace Zim\CertAuthBundle\Tests\Mocks;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TestAuthenticationChecker implements AuthorizationCheckerInterface
{

    public function isGranted($attributes, $object = null)
    {
        return true;
    }
}