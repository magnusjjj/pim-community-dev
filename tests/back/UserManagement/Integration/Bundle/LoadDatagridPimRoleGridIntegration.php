<?php

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Symfony\Component\HttpFoundation\Response;

final class LoadDatagridPimRoleGridIntegration extends ControllerIntegrationTestCase
{
    public function test_it_only_return_default_type_roles(): void
    {
        /** @var RoleInterface $roleRedactor */
        $roleRedactor = $this->get('pim_user.factory.role')->create();
        $roleRedactor->setRole('ROLE_REDACTOR');
        $roleRedactor->setLabel('Redactor');

        $this->get('pim_user.saver.role')->save($roleRedactor);

        /** @var RoleInterface $roleCatalogManager */
        $roleCatalogManager = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_CATALOG_MANAGER');
        $roleCatalogManager->setType('some_other_type');

        $this->get('pim_user.saver.role')->save($roleCatalogManager);

        /** @var RoleInterface $roleUser */
        $roleUser = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_USER');

        /** @var RoleInterface $roleAdministrator */
        $roleAdministrator = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_datagrid_load',
            [
                'alias' => 'pim-role-grid',
            ],
            'GET',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json']
        );
        $content = json_decode($response->getContent(), true);
        $data = json_decode($content['data'], true);

        $expectedResponseRoles = [
            [
                'label' => 'Administrator',
                'id' => (string) $roleAdministrator->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleAdministrator->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleAdministrator->getId()]),
            ],
            [
                'label' => 'User',
                'id' => (string) $roleUser->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleUser->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleUser->getId()]),
            ],
            [
                'label' => 'Redactor',
                'id' => (string) $roleRedactor->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleRedactor->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleRedactor->getId()]),
            ],
        ];

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertCount(3, $data['data']);

        foreach ($expectedResponseRoles as $expectedResponseRole) {
            self::assertContains($expectedResponseRole, $data['data']);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
