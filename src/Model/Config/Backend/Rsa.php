<?php

declare(strict_types=1);

namespace Factfinder\Export\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;

class Rsa extends File
{
    protected function getUploadDirPath($uploadDir): string
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::CONFIG)->getAbsolutePath($uploadDir);
    }
}
