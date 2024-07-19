<?php

namespace Flownative\Neos\MultisiteHelper\Command;

/*
 * This file is part of the Flownative.Neos.MultisiteHelper package.
 *
 * (c) Karsten Dambekalns, Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\ObjectManagement\Exception\UnresolvedDependenciesException;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * Command controller for site setup
 */
class MultisiteCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * Set up multi-site requirements, e.g. the needed asset collection.
     *
     * Call this (at least) once after a site has been installed.
     *
     * @param string $siteNodeName
     * @return void
     * @throws IllegalObjectTypeException|UnresolvedDependenciesException
     */
    public function setupCommand($siteNodeName): void
    {
        $assetCollectionTitle = ucfirst($siteNodeName);
        $assetCollection = $this->assetCollectionRepository->findOneByTitle($assetCollectionTitle);

        if ($assetCollection === null) {
            $assetCollection = new AssetCollection($assetCollectionTitle);
            $this->assetCollectionRepository->add($assetCollection);
        }

        /** @var Site $site */
        $site = $this->siteRepository->findOneByNodeName($siteNodeName);
        $site->setAssetCollection($assetCollection);
        $this->siteRepository->update($site);

        $this->outputLine('Site has been set up.');
    }
}
