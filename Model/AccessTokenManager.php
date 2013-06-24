<?php
namespace Publero\TokenAuthenticationBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Publero\Component\CodeGenerator\CodeGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessTokenManager implements AccessTokenManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var CodeGeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $accessTokenClass;

    /**
     * @param ObjectManager $om
     * @param CodeGeneratorInterface $generator
     * @param string $accessTokenClass
     */
    public function __construct(ObjectManager $om, CodeGeneratorInterface $generator, $accessTokenClass)
    {
        $this->om = $om;
        $this->generator = $generator;
        $this->accessTokenClass = $accessTokenClass;
    }

    public function findAccessToken($token)
    {
        return $this->om->getRepository($this->accessTokenClass)->findOneByToken($token);
    }

    public function generateAccessToken(UserInterface $user, array $roles = null)
    {
        if ($roles === null) {
            $roles = $user->getRoles();
        } else {
            $diff = array_diff($roles, $user->getRoles());
            if (count($diff) > 0) {
                throw \IllegalArgumentException(sprintf('User "%s" can\'t be assigned roles: %s',
                    $user->getUsername(),
                    implode(', ', $diff)
                ));
            }
        }

        $accessTokenRepository = $this->om->getRepository($this->accessTokenClass);
        do {
            $token = $this->generator->generate();
        } while ($accessTokenRepository->findOneByToken($token) !== null);

        $class = $this->accessTokenClass;
        $accessToken = new $class();

        try {
            $this->om->getClassMetadata($class);
            $accessToken->setUser($user);
        } catch(MappingException $e) {
            $accessToken->setUser($user->getUsername());
        }

        $accessToken->setToken($token);
        $accessToken->setRoles($roles);

        return $accessToken;
    }

    public function getManager()
    {
        return $this->om;
    }
}
