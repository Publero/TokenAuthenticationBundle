<?php
namespace Publero\TokenAuthenticationBundle\Tests\Model\AccessToken;

use Publero\TokenAuthenticationBundle\Model\AccessToken;
use Symfony\Component\Security\Core\User\User;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testSetUserAsUsername()
    {
        $username = 'test_username';
        $accessToken = new AccessToken();
        $accessToken->setUser($username);

        $this->assertEquals($username, $accessToken->getUser());
    }

    public function testSetUserAsObject()
    {
        $user = new User('username', 'password');
        $accessToken = new AccessToken();
        $accessToken->setUser($user);

        $this->assertSame($user, $accessToken->getUser());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetUserWrongType()
    {
        $accessToken = new AccessToken();
        $accessToken->setUser(array());
    }

    public function testAddRole()
    {
        $role = 'ROLE_TEST';
        $accessToken = new AccessToken();
        $accessToken->addRole($role);

        $this->assertEquals(array($role), $accessToken->getRoles());
    }

    public function testSetRolesStripsOldIndexes()
    {
        $role = 'ROLE_TEST';
        $accessToken = new AccessToken();
        $accessToken->setRoles(array('test.role.index' => $role));

        $this->assertEquals(array($role), $accessToken->getRoles());
    }

    public function testHasRole()
    {
        $role = 'ROLE_TEST';
        $accessToken = new AccessToken();
        $accessToken->addRole($role);

        $this->assertTrue($accessToken->hasRole($role));
    }

    public function testRemoveRole()
    {
        $role = 'ROLE_TEST';
        $accessToken = new AccessToken();
        $accessToken->addRole($role);
        $accessToken->removeRole($role);

        $this->assertEmpty($accessToken->getRoles());
    }

    public function testIsExpiredNotSet()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(null);

        $this->assertFalse($accessToken->isExpired());
    }

    public function testIsExpiredSetNotExpired()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(new \DateTime('now +1 hour'));

        $this->assertFalse($accessToken->isExpired());
    }

    public function testIsExpiredSetExpired()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(new \DateTime('now -1 second'));

        $this->assertTrue($accessToken->isExpired());
    }

    public function testIsValid()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(null);
        $accessToken->setRevoked(false);

        $this->assertTrue($accessToken->isValid());
    }

    public function testIsValidExpired()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(new \DateTime('now -1 second'));
        $accessToken->setRevoked(false);

        $this->assertFalse($accessToken->isValid());
    }

    public function testIsValidRevoked()
    {
        $accessToken = new AccessToken();
        $accessToken->setExpires(null);
        $accessToken->setRevoked(true);

        $this->assertFalse($accessToken->isValid());
    }
}
