<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadBusinessUnitData;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @dbIsolationPerTest
 */
class UserTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadUserData::class,
            LoadBusinessUnitData::class,
            LoadBusinessUnit::class
        ]);
        $role = $this->getEntityManager()
            ->getRepository(Role::class)
            ->findOneBy(['role' => 'ROLE_USER']);
        $this->getReferenceRepository()->addReference('ROLE_USER', $role);
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => 'users'],
            ['filter[email]' => LoadUserData::SIMPLE_USER_EMAIL]
        );

        $this->assertResponseContains('cget_user.yml', $response);
    }

    public function testGet()
    {
        $userId = $this->getReference(LoadUserData::SIMPLE_USER)->getId();

        $response = $this->get(
            ['entity' => 'users', 'id' => $userId]
        );

        $this->assertResponseContains('get_user.yml', $response);
        $this->assertResponseNotHasAttributes(
            ['password', 'plainPassword', 'salt', 'confirmationToken', 'emailLowercase'],
            $response
        );
    }

    public function testDelete()
    {
        $userId = $this->getReference(LoadUserData::SIMPLE_USER)->getId();

        $this->delete(
            ['entity' => 'users', 'id' => $userId]
        );

        $deletedUser = $this->getEntityManager()
            ->find(User::class, $userId);
        self::assertNull($deletedUser);
    }

    public function testDeleteList()
    {
        $this->cdelete(
            ['entity' => 'users'],
            ['filter[email]' => LoadUserData::SIMPLE_USER_EMAIL]
        );

        $deletedUser = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['email' => LoadUserData::SIMPLE_USER_EMAIL]);
        self::assertNull($deletedUser);
    }

    public function testCreate()
    {
        $ownerId = $this->getReference(LoadBusinessUnitData::BUSINESS_UNIT_1)->getId();
        $organizationId = $this->getReference('organization')->getId();

        $data = $this->getRequestData('create_user.yml');
        $response = $this->post(
            ['entity' => 'users'],
            'create_user.yml'
        );

        $userId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_user.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var User $user */
        $user = $this->getEntityManager()->find(User::class, $userId);
        self::assertEquals($organizationId, $user->getOrganization()->getId());
        self::assertEquals($ownerId, $user->getOwner()->getId());

        self::assertEmpty($user->getPlainPassword());
        self::assertNotEmpty($user->getPassword());
        self::assertNotEmpty($user->getSalt());
        /** @var PasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = self::getContainer()->get('security.encoder_factory')->getEncoder($user);
        self::assertTrue(
            $passwordEncoder->isPasswordValid(
                $user->getPassword(),
                $data['data']['attributes']['password'],
                $user->getSalt()
            )
        );
    }

    public function testCreateWithRequiredDataOnly()
    {
        $organizationId = $this->getReference('organization')->getId();

        $data = $this->getRequestData('create_user_min.yml');
        $response = $this->post(
            ['entity' => 'users'],
            $data
        );

        $userId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $this->assertResponseContains($responseContent, $response);

        /** @var User $user */
        $user = $this->getEntityManager()
            ->find(User::class, $userId);
        self::assertEquals($data['data']['attributes']['username'], $user->getUsername());
        self::assertEquals($data['data']['attributes']['firstName'], $user->getFirstName());
        self::assertEquals($data['data']['attributes']['lastName'], $user->getLastName());
        self::assertEquals($data['data']['attributes']['email'], $user->getEmail());
        self::assertEquals($organizationId, $user->getOrganization()->getId());
        self::assertEquals($this->getReference('business_unit'), $user->getOwner());

        self::assertEmpty($user->getPlainPassword());
        self::assertNotEmpty($user->getPassword());
        self::assertNotEmpty($user->getSalt());
    }

    public function testTryToCreateWithoutData()
    {
        $response = $this->post(
            ['entity' => 'users'],
            ['data' => ['type' => 'users']],
            [],
            false
        );

        $this->assertResponseValidationErrors(
            [
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/username']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/email']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/firstName']],
                ['title' => 'not blank constraint', 'source' => ['pointer' => '/data/attributes/lastName']]
            ],
            $response
        );
    }

    public function testCreateWithNullPassword()
    {
        $data = $this->getRequestData('create_user_min.yml');
        $data['data']['attributes']['password'] = null;
        $response = $this->post(
            ['entity' => 'users'],
            $data
        );

        /** @var User $user */
        $user = $this->getEntityManager()
            ->find(User::class, (int)$this->getResourceId($response));

        self::assertEmpty($user->getPlainPassword());
        self::assertNotEmpty($user->getPassword());
        self::assertNotEmpty($user->getSalt());
    }

    public function testCreateWithEmptyPassword()
    {
        $data = $this->getRequestData('create_user_min.yml');
        $data['data']['attributes']['password'] = '';
        $response = $this->post(
            ['entity' => 'users'],
            $data
        );

        /** @var User $user */
        $user = $this->getEntityManager()
            ->find(User::class, (int)$this->getResourceId($response));

        self::assertEmpty($user->getPlainPassword());
        self::assertNotEmpty($user->getPassword());
        self::assertNotEmpty($user->getSalt());
    }

    public function testTryToCreateWithInvalidPassword()
    {
        $data = $this->getRequestData('create_user_min.yml');
        $data['data']['attributes']['password'] = '1';
        $response = $this->post(
            ['entity' => 'users'],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'password complexity constraint',
                'source' => ['pointer' => '/data/attributes/password']
            ],
            $response
        );
    }

    public function testUpdate()
    {
        $userId = $this->getReference(LoadUserData::SIMPLE_USER)->getId();
        $businessUnitId = $this->getEntityManager()
            ->getRepository(BusinessUnit::class)
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT])
            ->getId();

        $data = [
            'data' => [
                'type'          => 'users',
                'id'            => (string)$userId,
                'attributes'    => [
                    'firstName' => 'Updated First Name'
                ],
                'relationships' => [
                    'owner' => [
                        'data' => ['type' => 'businessunits', 'id' => (string)$businessUnitId]
                    ]
                ]
            ]
        ];

        $this->patch(
            ['entity' => 'users', 'id' => $userId],
            $data
        );

        $user = $this->getEntityManager()->find(User::class, $userId);
        self::assertEquals('Updated First Name', $user->getFirstName());
        self::assertNotNull($user->getOwner(LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT));
    }

    public function testUpdateRelationshipForOwner()
    {
        $userId = $this->getReference(LoadUserData::SIMPLE_USER)->getId();
        $businessUnitId = $this->getEntityManager()
            ->getRepository(BusinessUnit::class)
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT])
            ->getId();

        $this->patchRelationship(
            ['entity' => 'users', 'id' => $userId, 'association' => 'owner'],
            [
                'data' => [
                    'type' => 'businessunits',
                    'id'   => (string)$businessUnitId
                ],
            ]
        );

        $user = $this->getEntityManager()->find(User::class, $userId);
        self::assertNotNull($user->getOwner(LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT));
    }

    public function testDeleteRelationshipForOwner()
    {
        $userId = $this->getReference(LoadUserData::SIMPLE_USER)->getId();

        $response = $this->deleteRelationship(
            ['entity' => 'users', 'id' => $userId, 'association' => 'owner'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET, PATCH');
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
    }
}
