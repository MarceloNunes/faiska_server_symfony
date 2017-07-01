<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Controller\Helper;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class UserRepository extends BaseRepository
{
    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->setEntityManager($entityManager)
            ->setClassName(User::CLASS_NAME)
            ->setClassAlias(User::CLASS_ALIAS)
            ->setOrderColumns(User::ORDER_COLUMNS);
    }

    private function setupBrowseWhereClause(QueryBuilder $queryBuilder, Helper\BrowseParameters $parameters)
    {
        $expr = $queryBuilder->expr();

        foreach ($parameters->getKeywords() as $keyword) {
            $queryBuilder->andWhere(
                $expr->orX(
                    $expr->like('user.name', "'%$keyword%'"),
                    $expr->like('user.email', "'%$keyword%'")
                )
            );
        }
    }

    /**
     * @param Helper\BrowseParameters $parameters
     * @return User[]
     */
    public function browseByKeyword(Helper\BrowseParameters $parameters)
    {
        $queryBuilder = $this->setupBrowseQueryBuilder($parameters);
        $this->setupBrowseWhereClause($queryBuilder, $parameters);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Helper\BrowseParameters $parameters
     * @return int
     */
    public function countByKeyword(Helper\BrowseParameters $parameters)
    {
        $queryBuilder = $this->setupCountQueryBuilder($parameters);
        $this->setupBrowseWhereClause($queryBuilder, $parameters);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
