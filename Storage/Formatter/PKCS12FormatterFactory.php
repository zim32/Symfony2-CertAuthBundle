<?php

namespace Zim\CertAuthBundle\Storage\Formatter;

class PKCS12FormatterFactory
{

    public static function createFormatter($options)
    {
        return new PKCS12Formatter();
    }

}