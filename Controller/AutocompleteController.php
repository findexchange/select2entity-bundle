<?php

namespace Tetranz\Select2EntityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AutocompleteController extends Controller
{
    /**
     * @param Request $ajaxRequest
     *
     * @return array
     */
    public function defaultAutocompleteAction(Request $ajaxRequest)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->get('doctrine')->getRepository($ajaxRequest->get('class'))->createQueryBuilder('q');

        $ajaxRequest->get('q');

        $entities = $qb->where(
            $qb->expr()->like('q.' . $ajaxRequest->get('text_property'), ':name')
        )
            ->setParameter('name', '%' . $ajaxRequest->get('q') . '%')
            ->setMaxResults($ajaxRequest->get('page_limit'))
            ->getQuery()
            ->getResult();

        $result = [];

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($entities as $entity) {
            $result[] = [
                'id' => $entity->getId(),
                'text' => $accessor->getValue($entity, $ajaxRequest->get('text_property')),
            ];
        }

        return new JsonResponse($result);
    }
}