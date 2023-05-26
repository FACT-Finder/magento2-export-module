<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Filesystem\Io;

use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\IoInterface;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\ObjectManagerInterface;

class UploadFactory
{
    public function __construct(private readonly ObjectManagerInterface $objectManager)
    {
    }

    public function create(array $params): IoInterface
    {
        $type =  Ftp::class;

        if ($params['type'] === 'sftp') {
            $type = $params['authentication_type'] === 'key' ? SftpPublicKeyAuth::class : Sftp::class;
        }

        return $this->objectManager->create($type);
    }
}
