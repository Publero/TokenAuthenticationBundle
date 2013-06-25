<?php
namespace Publero\TokenAuthenticationBundle\Security\Core\Authentication\Provider;

use Publero\TokenAuthenticationBundle\Security\Core\Authentication\Token\AccessTokenUserToken;
use Publero\TokenAuthenticationBundle\Model\AccessTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AccessTokenProvider implements AuthenticationProviderInterface
{
    /**
     * @var AccessTokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider, AccessTokenManagerInterface $accessTokenManager)
    {
        $this->accessTokenManager = $accessTokenManager;
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        if ($token->getCredentials() === null) {
            return null;
        }

        $accessToken = $this->accessTokenManager->findAccessToken($token->getAccessToken());

        if ($accessToken === null || !$accessToken->isValid()) {
            throw new AuthenticationException('Invalid access token.');
        }

        $user = $accessToken->getUser();
        if (is_string($user)) {
            $user = $this->userProvider->loadUserByUsername($user);
        }

        $roles = array_intersect($user->getRoles(), $accessToken->getRoles());
        $authenticatedToken = new AccessTokenUserToken($roles);
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuthenticated(true);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AccessTokenUserToken;
    }
}
