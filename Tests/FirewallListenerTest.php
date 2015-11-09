<?php

namespace Zim\CertAuthBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\User;
use Zim\CertAuthBundle\CertificateExpressionValidator;
use Zim\CertAuthBundle\Firewall\CertificateAuthenticationListener;
use Zim\CertAuthBundle\Security\Authentication\CertifiedUserToken;
use Zim\CertAuthBundle\Tests\Mocks\TestAuthenticationChecker;

class FirewallListenerTest extends KernelTestCase
{

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    protected $userProvider;
    protected $authChecker;
    /**
     * @var CertificateExpressionValidator
     */
    protected $expressionValidator;
    /**
     * @var CertificateAuthenticationListener
     */
    protected $authListener;

    public function setUp()
    {
        static::bootKernel();

        $tokenStorage =  self::$kernel->getContainer()->get('security.token_storage');

        $users = array(
            'zim32' => array('password'=>'password', 'enabled'=>true, 'roles'=>array('ROLE_ADMIN'))
        );
        $userProvider = new InMemoryUserProvider($users);


        $this->authChecker = new TestAuthenticationChecker();

        $logger = $this
            ->getMockBuilder('Symfony\Bridge\Monolog\Logger')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock()
        ;

        $this->tokenStorage = $tokenStorage;
        $this->expressionValidator = self::$kernel->getContainer()->get('zim_cert_auth.security.certificate_expression_validator');

        $this->authListener = new CertificateAuthenticationListener($tokenStorage, $userProvider, $this->authChecker, 'main', $logger, $this->expressionValidator);

    }

    public function testEmptyToken()
    {
        $request = new Request();
        $event = new GetResponseEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->tokenStorage->setToken(null);

        $res = $this->authListener->handle($event);
        $this->assertEmpty($res, 'Result must be null');
    }

    public function testAnonymousToken()
    {
        $request = new Request();
        $event = new GetResponseEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $token = new AnonymousToken('main', 'anon.');
        $token->setAuthenticated(true);
        $this->tokenStorage->setToken($token);

        $this->authListener->handle($event);
        if(!($this->tokenStorage->getToken() instanceof AnonymousToken)){
            $this->fail('Wrong token type after handle method');
        }
    }

    public function testUserNamePasswordToken()
    {
        $request = new Request();
        $event = new GetResponseEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        // test not authenticated token (no roles)
        $token = new UsernamePasswordToken('zim32', 'password', 'main', []);
        $this->tokenStorage->setToken($token);

        $this->authListener->handle($event);
        if(!($this->tokenStorage->getToken() instanceof UsernamePasswordToken)){
            $this->fail('Wrong token type after handle method');
        }

        // test authenticated token and no x509 provided
        $token = new UsernamePasswordToken('zim32', 'password', 'main', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->authListener->handle($event);
        $this->assertEquals(false, $token->isAuthenticated(), 'Token must not be authenticated after listener handle method');
        if(!($this->tokenStorage->getToken() instanceof UsernamePasswordToken)){
            $this->fail('Wrong token type after handle method');
        }
    }

    public function testExpressionValidation()
    {
        $x509 = file_get_contents(__DIR__.'/Fixtures/test.crt');
        $request = new Request();
        $request->server->set('CLIENT_CERT', $x509);
        $request->server->set('CLIENT_CERT_OK', 'SUCCESS');
        $this->authListener->setEnvCertificateContent('CLIENT_CERT');
        $this->expressionValidator->setExpression('cert["subject"]["CN"] == token.getUserName()');

        $event = new GetResponseEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $token = new UsernamePasswordToken('fake_user', 'password', 'main', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->authListener->handle($event);
        $this->assertEquals(false, $token->isAuthenticated(), 'Token must not be authenticated after listener handle method');
        if(!($this->tokenStorage->getToken() instanceof UsernamePasswordToken)){
            $this->fail('Wrong token type after handle method');
        }
    }

    public function testSuccessAuthentication()
    {
        $x509 = file_get_contents(__DIR__.'/Fixtures/test.crt');
        $request = new Request();
        $request->server->set('CLIENT_CERT', $x509);
        $request->server->set('CLIENT_CERT_OK', 'SUCCESS');
        $this->authListener->setEnvCertificateContent('CLIENT_CERT');
        $this->expressionValidator->setExpression('cert["subject"]["CN"] == token.getUserName()');

        $event = new GetResponseEvent(self::$kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $token = new UsernamePasswordToken('zim32', 'password', 'main', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->authListener->handle($event);
        $this->assertEquals(true, $token->isAuthenticated(), 'Token must be authenticated after listener handle method');
        if(!($this->tokenStorage->getToken() instanceof CertifiedUserToken)){
            $this->fail('Wrong token type after handle method');
        }
    }

}