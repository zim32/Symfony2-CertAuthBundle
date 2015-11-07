<?php

namespace Zim\CertAuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zim\CertAuthBundle\Exception\CertificateLoadingException;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;

class DumpCertificateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zim:cert:dump')
            ->setDescription('Dump client certificates')
            ->addArgument('identity', InputArgument::REQUIRED)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Secure password')
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output certificate to file')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Output in raw format')
            ->addOption('keyout', null, InputOption::VALUE_REQUIRED, 'Output private key to file')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identity = $input->getArgument('identity');
        $password = $input->getOption('password');
        $storage = $this->getContainer()->get('zim_cert_auth.certificate_storage');
        $out = $input->getOption('out');

        try{
            $cert = $storage->load($identity, $password);
        }catch (CertificateNotFoundException $e){
            $output->writeln('<error>Certificate not found</error>');
            exit(1);
        }catch (CertificateLoadingException $e){
            $output->writeln('<error>Certificate loading error. Maybe password protected?</error>');
            exit(2);
        }

        if($input->getOption('raw')){
            $content = $cert->getBinaryContent();
        }else{
            $x509 = $cert->getCert()->getX509();
            openssl_x509_export($x509, $content);
        }

        if($out){
            file_put_contents($out, $content);
        }else{
            $output->write($content, OutputInterface::OUTPUT_RAW);
        }

        $keyOut = $input->getOption('keyout');
        if($keyOut){
            $pkey = $cert->getCert()->getPrivateKey();
            openssl_pkey_export($pkey, $content);
            file_put_contents($keyOut, $content);
        }

    }


}