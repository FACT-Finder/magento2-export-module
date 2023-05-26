<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Filesystem\Io;

use Exception;
use Factfinder\Export\Model\Config\FtpConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\Sftp as SftpBase;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Net\SFTP;

class SftpPublicKeyAuth extends SftpBase
{
    public function __construct(
        private readonly Filesystem $fileSystem,
        private readonly FtpConfig $uploadConfig,
    ) {
    }

    public function open(array $args = []): void
    {
        $this->_connection = new SFTP($args['host'], $args['port'], self::REMOTE_TIMEOUT);

        if (!$this->_connection->login($args['user'], $this->getKey($args['key_passphrase']))) {
            throw new Exception(sprintf('Unable to open SFTP connection as %s@%s', $args['user'], $args['host']));
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getKey(string $passphrase): PrivateKey
    {
        $configDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::CONFIG);
        $filesInLocation = $configDirectory->read('factfinder/sftp');
        $keyFile = $configDirectory->readFile($filesInLocation[$this->getFileIndex($filesInLocation)]);
        $privateKey = PublicKeyLoader::loadPrivateKey($keyFile);

        if ($passphrase) {
            $privateKey = PublicKeyLoader::loadPrivateKey($keyFile, $passphrase);
        }

        return $privateKey;
    }

    /**
     * @throws FileSystemException
     */
    private function getFileIndex(array $directoryContent): int
    {
        $index = array_search($this->uploadConfig->getKeyFileName(), $directoryContent);

        if ($index === false) {
            throw new FileSystemException(__('The key file does not exist'));
        }

        return $index;
    }
}
