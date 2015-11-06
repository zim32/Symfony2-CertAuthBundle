<?php

namespace Zim\CertAuthBundle;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CertificateExpressionValidator
{

    protected $expression = '';
    protected $requestStack;
    protected $tokenStorage;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->expression = 'cert["subject"]["CN"] == token.getUserName() && request.server.get("CLIENT_CERT_OK") === "SUCCESS"';
    }

    public function validate(array $x509)
    {
        $request = $this->requestStack->getCurrentRequest();
        $token = $this->tokenStorage->getToken();

        $language = new ExpressionLanguage();

        return $language->evaluate($this->expression, ['request' => $request, 'token' => $token, 'cert' => $x509]);
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

}