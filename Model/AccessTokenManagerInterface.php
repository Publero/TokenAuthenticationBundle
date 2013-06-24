<?php
namespace Publero\TokenAuthenticationBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenManagerInterface
{
    /**
     * @param string $token
     * @return AccessToken|null
     */
    public function findAccessToken($token);

    /**
     * @param UserInterface $user
     * @param array $roles
     * @return AccessToken
     */
    public function generateAccessToken(UserInterface $user, array $roles = null);

    /**
     * @return ObjectManager
     */
    public function getManager();
}
