<?php

namespace Zim\CertAuthBundle\Storage\Persister;


use Zim\CertAuthBundle\CertificateStorageItem;
use Zim\CertAuthBundle\Exception\CertificateNotFoundException;

class LocalFilesystemPersister implements CertificatePersisterInterface
{

    protected $options;

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->validateOptions();
    }

    protected function validateOptions()
    {
        if (!isset($this->options['rootDir'])) {
            throw new \InvalidArgumentException('rootDir must be set');
        }

        if (!is_dir($this->options['rootDir']) || !is_writable($this->options['rootDir'])) {
            throw new \InvalidArgumentException('rootDir is not dir or is not writable');
        }
    }

    /**
     * @param CertificateStorageItem $item
     *
     * @throws \Exception
     */
    public function store(CertificateStorageItem $item)
    {
        $fileName = sprintf('%s.%s', $item->getIdentity(), implode($item->getExtensionPieces()));
        $path = rtrim($this->options['rootDir'], '/').'/'.$fileName;
        file_put_contents($path, $item->getBinaryContent());
    }

    /**
     * @param CertificateStorageItem $item
     * @throws CertificateNotFoundException
     *
     * This method must call setBinaryContent
     */
    public function load(CertificateStorageItem $item)
    {
        $fileGlob = rtrim($this->options['rootDir'], '/').'/'.$item->getIdentity().'.*';
        $res = glob($fileGlob);

        if (count($res) === 0) {
            throw new CertificateNotFoundException;
        }

        if (count($res) > 1) {
            throw new \LogicException('More then one certificate found.');
        }

        $item->setBinaryContent(file_get_contents($res[0]));
    }

    /**
     * @param $identity
     * @return bool
     * @throws CertificateNotFoundException
     */
    public function remove($identity)
    {
        $fileGlob = rtrim($this->options['rootDir'], '/').'/'.$identity.'.*';
        $res = glob($fileGlob);

        if (count($res) === 0) {
            throw new CertificateNotFoundException;
        }

        if (count($res) > 1) {
            throw new \LogicException('More then one certificate found.');
        }

        return unlink($res[0]);
    }
}