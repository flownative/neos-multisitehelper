<?php
namespace Flownative\Neos\MultisiteHelper;

/*
 * This file is part of the Flownative.Neos.MultisiteHelper package.
 *
 * (c) Karsten Dambekalns, Flownative GmbH - www.flownative.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityNotFoundException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Authentication\Provider\PersistedUsernamePasswordProvider as FlowPersistedUsernamePasswordProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception;
use Neos\Flow\Security\Exception\InvalidAuthenticationStatusException;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Utility\ObjectAccess;
use Psr\Log\LoggerInterface;

/**
 * A custom site aware authentication provider extending the existing persisted username password provider
 */
class PersistedUsernamePasswordProvider extends FlowPersistedUsernamePasswordProvider
{
    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject(name="Neos.Flow:SecurityLogger")
     * @var LoggerInterface
     */
    protected $securityLogger;

    /**
     * Checks if a user has access to a site by enabling entity privileges and checking if user has access to current
     * site.
     *
     * @param TokenInterface $authenticationToken The token to be authenticated
     * @return void
     * @throws IllegalObjectTypeException
     * @throws Exception
     * @throws InvalidAuthenticationStatusException
     * @throws NoSuchRoleException
     * @throws UnsupportedAuthenticationTokenException
     */
    public function authenticate(TokenInterface $authenticationToken): void
    {
        parent::authenticate($authenticationToken);
        if (!$authenticationToken->getAuthenticationStatus() === TokenInterface::AUTHENTICATION_SUCCESSFUL) {
            return;
        }

        // Force set isAuthenticated to true on authentication manager to update roles needed for entity privileges
        ObjectAccess::setProperty($this->authenticationManager, 'isAuthenticated', true, true);

        $domain = $this->domainRepository->findOneByActiveRequest();
        if ($domain === null) {
            if (!$this->securityContext->hasRole('Neos.Neos:Administrator')) {
                $this->securityLogger->log('No domain found and user has not assigned "Neos.Neos:Administrator", rolling back.', LOG_DEBUG);
                $this->rollback($authenticationToken);
            }
            return;
        }

        $site = $domain->getSite();
        try {
            $site->getName();
            // Site access allowed, continue
        } catch (EntityNotFoundException $e) {
            $this->securityLogger->log('Domain found but user has no access to site, rolling back.', LOG_DEBUG);
            $this->rollback($authenticationToken);
        }
    }

    /**
     * @param TokenInterface $authenticationToken
     * @return void
     */
    protected function rollback(TokenInterface $authenticationToken): void
    {
        $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
        ObjectAccess::setProperty($this->authenticationManager, 'isAuthenticated', false, true);
        $this->policyService->reset();
        $this->securityContext->refreshRoles();
    }
}
