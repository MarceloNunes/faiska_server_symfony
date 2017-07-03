<?php

namespace AppBundle\Repository;

use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Controller\Helper;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;

class User extends BaseRepository
{
    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->setEntityManager($entityManager)
            ->setClassName(Entity\User::CLASS_NAME)
            ->setClassAlias(Entity\User::CLASS_ALIAS)
            ->setOrderColumns(Entity\User::ORDER_COLUMNS);
    }

    private function setupBrowseWhereClause(QueryBuilder $queryBuilder, Helper\BrowseParameters $parameters)
    {
        $expr = $queryBuilder->expr();

        foreach ($parameters->getKeywords() as $keyword) {
            $keyword = str_replace('\'', '\'\'', $keyword);

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
        $queryBuilder = $this->setupCountQueryBuilder();
        $this->setupBrowseWhereClause($queryBuilder, $parameters);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int
     */
    public function countAll()
    {
        $queryBuilder = $this->setupCountQueryBuilder();
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $userHash
     * @return Entity\User
     * @throws EntityNotFoundException
     */
    public function getByHash($userHash) {
        $user = $this->entityManager->getRepository(Entity\User::CLASS_NAME)->findOneByHash($userHash);

        if (!$user) {
            throw new EntityNotFoundException();
        }

        return $user;
    }
}
