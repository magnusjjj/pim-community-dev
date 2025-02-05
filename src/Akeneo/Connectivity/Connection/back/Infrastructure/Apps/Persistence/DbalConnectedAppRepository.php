<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalConnectedAppRepository implements ConnectedAppRepositoryInterface
{
    private DbalConnection $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function findAll(): array
    {
        $selectSQL = <<<SQL
        SELECT
               id,
               connected_app.name,
               scopes,
               connection_code,
               logo,
               author,
               user_group_name,
               categories,
               certified,
               partner,
               IF(akeneo_connectivity_test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app
        FROM akeneo_connectivity_connected_app AS connected_app
        LEFT JOIN akeneo_connectivity_test_app ON akeneo_connectivity_test_app.client_id = connected_app.id
        ORDER BY name ASC
        SQL;

        $dataRows = $this->dbalConnection->executeQuery($selectSQL)->fetchAll();

        $connectedApps = array_map(
            fn ($dataRow) => $this->denormalizeRow($dataRow),
            $dataRows
        );

        return $connectedApps;
    }

    public function create(ConnectedApp $app): void
    {
        $insertQuery = <<<SQL
        INSERT INTO akeneo_connectivity_connected_app (id, name, logo, author, partner, categories, scopes, certified, connection_code, user_group_name)
        VALUES (:id, :name, :logo, :author, :partner, :categories, :scopes, :certified, :connection_code, :user_group_name)
        SQL;

        $this->dbalConnection->executeQuery(
            $insertQuery,
            [
                'id' => $app->getId(),
                'name' => $app->getName(),
                'logo' => $app->getLogo(),
                'author' => $app->getAuthor(),
                'partner' => $app->getPartner(),
                'categories' => $app->getCategories(),
                'scopes' => $app->getScopes(),
                'certified' => $app->isCertified(),
                'connection_code' => $app->getConnectionCode(),
                'user_group_name' => $app->getUserGroupName(),
            ],
            [
                'certified' => Types::BOOLEAN,
                'categories' => Types::JSON,
                'scopes' => Types::JSON,
            ]
        );
    }

    public function findOneById(string $appId): ?ConnectedApp
    {
        $selectQuery = <<<SQL
        SELECT
            connected_app.id,
            connected_app.name,
            connected_app.logo,
            connected_app.author,
            connected_app.partner,
            connected_app.categories,
            connected_app.scopes,
            connected_app.certified,
            connected_app.connection_code,
            connected_app.user_group_name,
            IF(test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app
        FROM akeneo_connectivity_connected_app connected_app
        LEFT JOIN akeneo_connectivity_test_app test_app ON test_app.client_id = connected_app.id
        WHERE id = :id
        SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['id' => $appId])->fetchAssociative();

        return $dataRow ? $this->denormalizeRow($dataRow) : null;
    }

    public function findOneByConnectionCode(string $connectionCode): ?ConnectedApp
    {
        $selectQuery = <<<SQL
        SELECT
            connected_app.id,
            connected_app.name,
            connected_app.logo,
            connected_app.author,
            connected_app.partner,
            connected_app.categories,
            connected_app.scopes,
            connected_app.certified,
            connected_app.connection_code,
            connected_app.user_group_name,
            IF(test_app.client_id IS NULL, FALSE, TRUE) AS is_test_app
        FROM akeneo_connectivity_connected_app connected_app
        LEFT JOIN akeneo_connectivity_test_app test_app ON test_app.client_id = connected_app.id
        WHERE connection_code = :connectionCode
        SQL;

        $dataRow = $this->dbalConnection->executeQuery($selectQuery, ['connectionCode' => $connectionCode])->fetchAssociative();

        return $dataRow ? $this->denormalizeRow($dataRow) : null;
    }

    /**
     * @param array{
     *    id: string,
     *    name: string,
     *    scopes: string,
     *    connection_code: string,
     *    logo: string,
     *    author: string,
     *    user_group_name: string,
     *    categories: string,
     *    certified: bool,
     *    partner: ?string,
     * } $dataRow
     */
    private function denormalizeRow(array $dataRow): ConnectedApp
    {
        return new ConnectedApp(
            $dataRow['id'],
            $dataRow['name'],
            \json_decode($dataRow['scopes'], true),
            $dataRow['connection_code'],
            $dataRow['logo'],
            $dataRow['author'],
            $dataRow['user_group_name'],
            \json_decode($dataRow['categories'], true),
            (bool) $dataRow['certified'],
            $dataRow['partner'],
            (bool) ($dataRow['is_test_app'] ?? false),
        );
    }
}
