<?php

namespace CPSIT\T3importExport\Resource;

use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Trait ResourceStorageTrait
 * Provides a resource storage
 */
trait ResourceStorageTrait
{
    use StorageRepositoryTrait;

    /**
     * Initializes the resource resourceStorage
     *
     * @param array $configuration
     */
    public function initializeStorage($configuration)
    {
        $this->resourceStorage = $this->storageRepository->findByUid($configuration['storageId']);
    }

    /**
     * @param ResourceStorageInterface $resourceStorage
     * @return $this
     * @deprecated Inject dependency via constructor instead
     */
    public function withStorage(ResourceStorageInterface $resourceStorage): self
    {
        $this->resourceStorage = $resourceStorage;
        return $this;
    }
}
