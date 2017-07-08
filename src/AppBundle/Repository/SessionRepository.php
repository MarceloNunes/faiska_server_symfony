<?php

namespace AppBundle\Repository;

use AppBundle\Controller\Helper;
use AppBundle\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;

class SessionRepository extends BaseRepository
{
    /**
     * SessionRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->setEntityManager($entityManager)
            ->setClassName(Entity\Session::CLASS_NAME)
            ->setClassAlias(Entity\Session::CLASS_ALIAS)
            ->setOrderColumns(Entity\Session::ORDER_COLUMNS);
    }

    /**
     * @param Entity\User $user
     * @param Helper\BrowseParameters $parameters
     * @return Entity\Session[]
     */
    public function browseByUser(Entity\User $user, Helper\BrowseParameters $parameters)
    {
        $queryBuilder = $this->setupBrowseQueryBuilder($parameters);

        $this->setupUserJoin($user, $queryBuilder);

        echo $queryBuilder->getDQL();

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Entity\User $user
     * @return int
     */
    public function countByUser(Entity\User $user)
    {
        $queryBuilder = $this->setupCountQueryBuilder();

        $this->setupUserJoin($user, $queryBuilder);

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
     * @param string $sessionHash
     * @return Entity\Session
     * @throws EntityNotFoundException
     */
    public function getByHash($sessionHash) {
        $session = $this
            ->entityManager
            ->getRepository(Entity\Session::CLASS_NAME)
            ->findOneBy(array(
                'hash' => $sessionHash
            ));

        if (!$session) {
            throw new EntityNotFoundException();
        }

        return $session;
    }

    /**
     * @param Entity\User $user
     * @param QueryBuilder $queryBuilder
     */
    private function setupUserJoin(Entity\User $user, QueryBuilder $queryBuilder)
    {
        $queryBuilder->join(
            Entity\Session::CLASS_ALIAS . '.' . Entity\User::CLASS_ALIAS,
            Entity\User::CLASS_ALIAS
        );

        $queryBuilder->where(
            $queryBuilder->expr()->eq(Entity\User::CLASS_ALIAS . '.id', '?1')
        );

        $queryBuilder->setParameter(1, $user->getId());
    }
}