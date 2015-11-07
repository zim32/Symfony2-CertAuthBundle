<?php

namespace Zim\CertAuthBundle\Storage\Persister;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Zim\CertAuthBundle\CertificateAwareUserInterface;
use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;
use Zim\CertAuthBundle\Storage\Entity\CertificateEntity;

class ORMPersister implements CertificatePersisterInterface
{

    protected $options;
    protected $em;
    protected $tokenStorage;

    public function __construct(array $options, EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->options = $options;
    }

    /**
     * @param CertificateStorageItem $item
     *
     */
    public function store(CertificateStorageItem $item)
    {
        $user = $this->tokenStorage->getToken();
        $flushUser = false;

        $entity = new CertificateEntity();
        $entity->setContent($item->getBinaryContent());
        $entity->setIdentity($item->getIdentity());

        if($user instanceof CertificateAwareUserInterface){
            $user->setCertificate($entity);
            $flushUser = isset($this->options['flush_user']) ? $this->options['flush_user'] : true;
        }

        $this->em->persist($entity);
        $this->em->flush($entity);
        if($flushUser){
            $this->em->flush($user);
        }
    }

    /**
     * @param CertificateStorageItem $item
     * @throws CertificateNotFoundException
     *
     * This method must call setBinaryContent
     */
    public function load(CertificateStorageItem $item)
    {
        $entity = $this->em->getRepository('Zim\CertAuthBundle\Storage\Entity\CertificateEntity')->findOneBy(['identity'=>$item->getIdentity()]);
        if($entity){
            $content = stream_get_contents($entity->getContent());
            $item->setBinaryContent($content);
        }else{
            throw new CertificateNotFoundException;
        }
    }
}