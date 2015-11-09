<?php

namespace Zim\CertAuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zim\CertAuthBundle\Exception\CertificateLoadingException;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;

class RemoveCertificateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zim:cert:remove')
            ->setDescription('Remove client certificate from storage')
            ->addArgument('identity', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identity = $input->getArgument('identity');
        $storage = $this->getContainer()->get('zim_cert_auth.certificate_storage');

        try{
            $res = $storage->remove($identity);
        }catch (CertificateNotFoundException $e){
            $output->writeln("<comment>Certificate was not found</comment>");
            return;
        }

        if($res){
            $output->writeln("<info>Certificate was removed successfully</info>");
        }else{
            $output->writeln("<error>Can't remove certificate. Unknown error</error>");
            exit(2);
        }
    }


}