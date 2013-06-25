<?php
namespace Publero\TokenAuthenticationBundle\Tests\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Publero\Component\CodeGenerator\CodeGeneratorInterface;
use Publero\TokenAuthenticationBundle\Model\AccessTokenManager;
use Symfony\Component\Security\Core\User\User;

class AccessTokenManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ObjectRepository
     */
    private $repo;

    /**
     * @var CodeGeneratorInterface
     */
    private $generator;

    /**
     * @var AccessTokenManager
     */
    private $accessManager;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->om
            ->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object) array('name' => 'Publero\TokenAuthenticationBundle\Model\AccessToken')))
        ;
        $this->om
            ->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null))
        ;
        $this->om
            ->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null))
        ;

        $this->repo = $this->getMock('Doctrine\Common\Persistence\ObjectRepository', array('findOneByToken'), array(), '', false, false, false);
        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->with('Publero\TokenAuthenticationBundle\Model\AccessToken')
            ->will($this->returnValue($this->repo))
        ;

        $this->generator = $this->getMock('Publero\Component\CodeGenerator\Md5HashGenerator', array('generate'), array(), '', false);

        $this->accessManager = new AccessTokenManager($this->om, $this->generator, 'Publero\TokenAuthenticationBundle\Model\AccessToken');
    }

    public function testFindAccessToken()
    {
        $token = $this->generator->generate();
        $this->repo
            ->expects($this->once())
            ->method('findOneByToken')
            ->with($token)
        ;

        $this->accessManager->findAccessToken($token);
    }

    public function testGenerateAccessToken()
    {
        $this->repo
            ->expects($this->any())
            ->method('findOneByToken')
            ->will($this->returnValue(null))
        ;

        $token = 'test_access_token';
        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($token))
        ;

        $user = new User('username', 'password', array('ROLE_TEST_FIRST', 'ROLE_TEST_SECOND'));
        $accessToken = $this->accessManager->generateAccessToken($user, array('ROLE_TEST_SECOND'));

        $this->assertSame($user, $accessToken->getUser());
        $this->assertNotEmpty($accessToken->getToken());
        $this->assertEquals(array('ROLE_TEST_SECOND'), $accessToken->getRoles());
    }

    public function testGetManager()
    {
        $this->assertSame($this->om, $this->accessManager->getManager());
    }
}
