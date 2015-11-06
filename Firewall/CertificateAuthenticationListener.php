<?php

namespace Zim\CertAuthBundle\Firewall;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Zim\CertAuthBundle\CertificateExpressionValidator;
use Zim\CertAuthBundle\Security\Authentication\CertifiedUserToken;

class CertificateAuthenticationListener implements ListenerInterface
{

    protected $tokenStorage;
    protected $userProvider;
    protected $authChecker;
    protected $providerKey;
    protected $envCertificateContent;
    protected $logger;
    protected $expValidator;
    protected $customOIDs = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserProviderInterface $userProvider,
        AuthorizationChecker $authChecker,
        $providerKey,
        LoggerInterface $logger,
        CertificateExpressionValidator $expValidator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->authChecker = $authChecker;
        $this->providerKey = $providerKey;
        $this->logger = $logger;
        $this->expValidator = $expValidator;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $token = $this->tokenStorage->getToken();
        if (!$token || !($token->isAuthenticated()) || $token instanceof AnonymousToken) {
            return;
        }

        $x509 = trim($request->server->get($this->envCertificateContent));
        if (!$x509) {
            $this->logger->debug('Client certificate is not provided.');
            $token->setAuthenticated(false);

            return;
        }

        $x509 = openssl_x509_parse($x509);
        if (!$x509) {
            $this->logger->error(sprintf('Can not parse client certificate. %s', openssl_error_string()));
            $token->setAuthenticated(false);

            return;
        }

        if (!$this->expValidator->validate($x509)) {
            $this->logger->warning('Certificate expression validation failed.');
            $token->setAuthenticated(false);

            return;
        }

        if (!($token->getUser() instanceof UserInterface)) {
            $userName = $x509['subject']['CN'];
            $user = $this->userProvider->loadUserByUsername($userName);
        } else {
            // here we already have refreshed user from ContextListener so just pick it up
            $user = $token->getUser();
        }

        $roles = $token->getRoles();

        if (!$this->authChecker->isGranted('ROLE_CERT_AUTHENTICATED_FULLY')) {
            array_push($roles, 'ROLE_CERT_AUTHENTICATED_FULLY');
        }

        $token = new CertifiedUserToken($user, $token->getCredentials(), $this->providerKey, $roles);
        $token->setX509($x509);
        $this->tokenStorage->setToken($token);

    }

    /**
     * @param string $envCertificateContent
     */
    public function setEnvCertificateContent($envCertificateContent)
    {
        $this->envCertificateContent = $envCertificateContent;
    }

    /**
     * @param array $customOIDs
     */
    public function setCustomOIDs($customOIDs)
    {
        $this->customOIDs = $customOIDs;
    }

}