<?php

namespace AppBundle\Repository;

use AppBundle\Entity;
use AppBundle\Exception\Http\BadRequestException;
use AppBundle\Repository\Helper\Validator;
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

    /**
     * @param QueryBuilder $queryBuilder
     * @param Helper\BrowseParameters $parameters
     */
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
        $user = $this
            ->entityManager
            ->getRepository(Entity\User::CLASS_NAME)
            ->findOneByHash($userHash);

        if (!$user) {
            throw new EntityNotFoundException();
        }

        return $user;
    }

    /**
     * @param Helper\UnifiedRequest $request
     * @param $ctrlValidator
     * @return Entity\User
     * @throws BadRequestException
     */
    public function insert(Helper\UnifiedRequest $request, $ctrlValidator)
    {
        $userValidator = new Validator\User($request);
        $userValidator->validate($this->entityManager, $ctrlValidator);

        $user = new Entity\User();
        $user
            ->setEmail($request->get('email'))
            ->setSecret($request->get('secret'))
            ->setName($request->get('name'))
            ->setCreatedAt(new \DateTime('now'))
            ->setHash()
            ->activate();

        if ($request->isProvided('birthDate')) {
            $user->setBirthDate(new \DateTime($request->get('birthDate')));
        }

        if ($this->countAll() == 0) {
            $user->setAdmin();
        } else {
            $user->unsetAdmin();
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param $userHash
     * @param Helper\UnifiedRequest $request
     * @param $ctrlValidator
     * @return Entity\User
     * @throws EntityNotFoundException
     * @throws BadRequestException
     */
    public function update($userHash, Helper\UnifiedRequest $request, $ctrlValidator)
    {
        /** @var Entity\User $user */
        $user = $this
            ->entityManager
            ->getRepository(Entity\User::CLASS_NAME)
            ->findOneByHash($userHash);

        if (!$user) {
            throw new EntityNotFoundException();
        }

        $userValidator = new Validator\User($request);
        $userValidator->validate($this->entityManager, $ctrlValidator, $user->getId());

        if ($request->isProvided('name')) {
            $user->setName($request->get('name'));
        }

        if ($request->isProvided('email')) {
            $user->setEmail($request->get('email'));
        }

        if ($request->isProvided('secret')) {
            $user->setSecret($request->get('secret'));
        }

        if ($request->isProvided('active')) {
            if ((int) $request->get('active')) {
                $user->activate();
            } else {
                $user->deactivate();
            }
        }

        if ($request->isProvided('admin')) {
            if ((int) $request->get('admin')) {
                $user->setAdmin();
            } else {
                $user->unsetAdmin();
            }
        }

        if ($request->isProvided('birthDate')) {
            $user->setBirthDate(new \DateTime($request->get('birthDate')));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
