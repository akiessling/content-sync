<?php

declare(strict_types=1);

namespace B13\ContentSync\Domain\Model;

/*
 * This file is part of TYPO3 CMS-based extension "content-sync" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\ContentSync\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    protected array $databaseTables = [];
    protected array $excludeDatabaseTables = [];
    protected array $syncFiles = [];
    protected Node $targetNode;
    protected Node $sourceNode;

    public function fromExtensionConfiguration(?array $extensionConfiguration): Configuration
    {
        if ($extensionConfiguration === null) {
            throw new Exception('content_sync extension configuration is missing', 1600765845);
        }
        if (!isset($extensionConfiguration['configuration']) || !is_array($extensionConfiguration['configuration'])) {
            throw new Exception('content_sync extension configuration section is missing', 1600765846);
        }
        if (!isset($extensionConfiguration['targetNode']) || !is_array($extensionConfiguration['targetNode'])) {
            throw new Exception('content_sync target node configuration is missing', 1600765847);
        }
        if (!isset($extensionConfiguration['sourceNode']) || !is_array($extensionConfiguration['sourceNode'])) {
            throw new Exception('content_sync source node configuration is missing', 1600765848);
        }
        foreach (['databaseTables', 'excludeDatabaseTables', 'syncFiles'] as $key) {
            if (!array_key_exists($key, $extensionConfiguration['configuration'])) {
                throw new Exception('content_sync configuration value "' . $key . '" is missing', 1600765849);
            }
        }
        foreach (['targetNode', 'sourceNode'] as $nodeKey) {
            foreach (['local', 'connection', 'basePath', 'bin'] as $key) {
                if (!array_key_exists($key, $extensionConfiguration[$nodeKey])) {
                    throw new Exception('content_sync ' . $nodeKey . ' configuration value "' . $key . '" is missing', 1600765850);
                }
            }
        }

        $this->databaseTables = GeneralUtility::trimExplode(',', (string)$extensionConfiguration['configuration']['databaseTables'], true);
        $this->excludeDatabaseTables = GeneralUtility::trimExplode(',', (string)$extensionConfiguration['configuration']['excludeDatabaseTables'], true);
        $this->syncFiles = GeneralUtility::trimExplode(',', (string)$extensionConfiguration['configuration']['syncFiles'], true);
        $this->targetNode = (new Node())->fromArray($extensionConfiguration['targetNode']);
        $this->sourceNode = (new Node())->fromArray($extensionConfiguration['sourceNode']);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDatabaseTables(): array
    {
        return $this->databaseTables;
    }

    /**
     * @return string[]
     */
    public function getExcludeDatabaseTables(): array
    {
        return $this->excludeDatabaseTables;
    }

    /**
     * @return string[]
     */
    public function getSyncFiles(): array
    {
        return $this->syncFiles;
    }

    public function getTargetNode(): Node
    {
        return $this->targetNode;
    }

    public function getSourceNode(): Node
    {
        return $this->sourceNode;
    }

    public function getRemoteNode(): ?Node
    {
        if (!$this->getTargetNode()->isLocal()) {
            return $this->getTargetNode();
        }
        if (!$this->getSourceNode()->isLocal()) {
            return $this->getSourceNode();
        }
        return null;
    }
}
