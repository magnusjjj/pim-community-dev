<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Domain\Query\FindJobUsersInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetJobUsersAction
{
    private Security $security;
    private SecurityFacade $securityFacade;
    private FindJobUsersInterface $findJobUsers;

    public function __construct(
        Security $security,
        SecurityFacade $securityFacade,
        FindJobUsersInterface $findJobUsers
    ) {
        $this->security = $security;
        $this->securityFacade = $securityFacade;
        $this->findJobUsers = $findJobUsers;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pim_enrich_job_tracker_index')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list job users.');
        }

        if (!$this->securityFacade->isGranted('pim_enrich_job_tracker_view_all_jobs')) {
            return new JsonResponse([$this->security->getUser()->getUserIdentifier()]);
        }

        $page = (int) $request->get('page', 1);
        $jobUsers = $this->findJobUsers->search($page);

        return new JsonResponse($jobUsers);
    }
}