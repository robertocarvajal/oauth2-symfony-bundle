<?php

/**
 * This file is part of the authbucket/oauth2-php package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\OAuth2\Symfony\Component\Security\Http\Firewall;

use AuthBucket\OAuth2\Exception\InvalidRequestException;
use AuthBucket\OAuth2\Symfony\Component\Security\Core\Authentication\Token\ClientCredentialsToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * TokenListener implements OAuth2 token endpoint authentication.
 *
 * @author Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 */
class TokenListener
{
    protected $providerKey;
    protected $tokenStorage;
    protected $authenticationManager;
    protected $validator;
    protected $logger;
    protected $clientTokenRoles;

    public function __construct(
        $providerKey,
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        array $clientTokenRoles = []
    ) {
        $this->providerKey = $providerKey;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->clientTokenRoles = $clientTokenRoles;
    }

    public function __invoke(RequestEvent $event)
    {
        $this->handle($event);
    }

    public function handle(RequestEvent $event)
    {
        $request = $event->getRequest();

        // At least one of client credentials method required.
        // If more than one is set then basic auth will be used. Some clients (incorrectly) use more than one.
        if (!$request->headers->get('PHP_AUTH_USER', false) && !$request->request->get('client_id', false)) {
            throw new InvalidRequestException([
                'error_description' => 'The request is missing a required parameter',
            ]);
        }

        // Check with HTTP basic auth if exists.
        if ($request->headers->get('PHP_AUTH_USER', false)) {
            $clientId = $request->headers->get('PHP_AUTH_USER', false);
            $clientSecret = $request->headers->get('PHP_AUTH_PW', false);
        } else {
            $clientId = $request->request->get('client_id', false);
            $clientSecret = $request->request->get('client_secret', false);
        }

        // If there is no client then dont continue
        if ($clientId === false && $clientSecret === false) {
            return;
        }

        // client_id must in valid format.
        $errors = $this->validator->validate($clientId, [
            new \Symfony\Component\Validator\Constraints\NotBlank(),
            new \AuthBucket\OAuth2\Symfony\Component\Validator\Constraints\ClientId(),
        ]);
        if (count($errors) > 0) {
            throw new InvalidRequestException([
                'error_description' => 'The request includes an invalid parameter value.',
            ]);
        }

        // client_secret must in valid format.
        $errors = $this->validator->validate($clientId, [
            new \Symfony\Component\Validator\Constraints\NotBlank(),
            new \AuthBucket\OAuth2\Symfony\Component\Validator\Constraints\ClientSecret(),
        ]);
        if (count($errors) > 0) {
            throw new InvalidRequestException([
                'error_description' => 'The request includes an invalid parameter value.',
            ]);
        }

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Token endpoint client credentials found for client_id "%s"', $clientId));
        }

        if (null !== $token = $this->tokenStorage->getToken()) {
            if ($token instanceof ClientCredentialsToken
                && $token->isAuthenticated()
                && $token->getClientId() === $clientId
            ) {
                return;
            }
        }

        $token = new ClientCredentialsToken(
            $this->providerKey,
            $clientId,
            $clientSecret,
            '',
            $this->clientTokenRoles
        );
        $tokenAuthenticated = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($tokenAuthenticated);
    }
}
