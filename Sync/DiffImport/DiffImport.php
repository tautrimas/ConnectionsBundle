<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffImport;

use ONGR\ConnectionsBundle\Sync\SyncStorage\SyncStorageInterface;

/**
 * Class for import sync data to ElasticSearch.
 */
abstract class DiffImport
{
    /**
     * @var SyncStorageInterface
     */
    private $storage;

    /**
     * Imports sync data to ElasticSearch.
     */
    abstract public function import();

    /**
     * Set SyncStorage storage facility.
     *
     * @param SyncStorageInterface $storage
     */
    public function setStorageFacility(SyncStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * SyncStorage storage facility.
     *
     * @return SyncStorageInterface
     */
    public function getStorageFacility()
    {
        return $this->storage;
    }
}
