<?php

namespace Zim\CertAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Zim\CertAuthBundle\Exception\CertificateLoadingException;

class AccessDeniedController extends Controller
{

    public function indexAction(Request $request)
    {
        // we are safe here not to check for IS_AUTHENTICATED_FULLY because of ACL have done this for us
        $token = $this->get('security.token_storage')->getToken();

        $certStorage = $this->get('zim_cert_auth.certificate_storage');
        if ($certStorage->has($token->getUsername())) {
            return $this->redirectToRoute('zim_cert_auth_denied_restore');
        }

        if ($request->getMethod() === 'POST') {
            $password = $request->request->get('password');

            return $this->generateCertificate($password);
        }

        return $this->render('ZimCertAuthBundle:Denied:index.html.twig');
    }

    public function restoreAction(Request $request)
    {
        $error = '';
        $token = $this->get('security.token_storage')->getToken();

        $certStorage = $this->get('zim_cert_auth.certificate_storage');
        if (!$certStorage->has($token->getUsername())) {
            return $this->redirectToRoute('zim_cert_auth_denied_index');
        }

        if ($request->getMethod() === 'POST') {
            $password = $request->request->get('password');
            try {
                return $this->restoreCertificate($password);
            } catch (CertificateLoadingException $e) {
                $error = 'Oops. Your certificate could not be loaded. Check your password or contact administrator.';
            }
        }

        return $this->render('ZimCertAuthBundle:Denied:restore.html.twig', ['error' => $error]);
    }

    protected function generateCertificate($password)
    {
        $token = $this->get('security.token_storage')->getToken();
        $cert = $this->get('zim_cert_auth.certificate_generator')->generateClientCertificate($token);
        $storedItem = $this->get('zim_cert_auth.certificate_storage')->store($cert, $token->getUsername(), $password);

        $response = new Response($storedItem->getBinaryContent());
        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $storedItem->getIdentity().'.'.implode('.', $storedItem->getExtensionPieces())
        );
        $response->headers->set('Content-Disposition', $d);

        return $response;

    }

    protected function restoreCertificate($password)
    {
        $token = $this->get('security.token_storage')->getToken();
        $storedItem = $this->get('zim_cert_auth.certificate_storage')->load($token->getUsername(), $password);

        $response = new Response($storedItem->getBinaryContent());
        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $storedItem->getIdentity().'.'.implode('.', $storedItem->getExtensionPieces())
        );
        $response->headers->set('Content-Disposition', $d);

        return $response;

    }

}