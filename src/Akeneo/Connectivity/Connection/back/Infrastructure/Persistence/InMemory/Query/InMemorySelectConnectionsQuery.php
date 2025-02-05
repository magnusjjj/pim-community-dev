<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemorySelectConnectionsQuery implements SelectConnectionsQueryInterface
{
    private InMemoryConnectionRepository $connectionRepository;

    public function __construct(InMemoryConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * @param string[] $types
     * @return Connection[]
     */
    public function execute(array $types = []): array
    {
        $connections = [];
        foreach ($this->connectionRepository->dataRows as $dataRow) {
            $connections[] = new Connection(
                $dataRow['code'],
                $dataRow['label'],
                $dataRow['flow_type'],
                $dataRow['image'],
                $dataRow['auditable']
            );
        }

        return $connections;
    }
}
