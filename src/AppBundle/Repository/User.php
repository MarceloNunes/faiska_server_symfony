<?php

namespace AppBundle\Repository;

use AppBundle\Entity;
use AppBundle\Exception\Http\BadRequest;
use AppBundle\Repository\Helper\Validator;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Controller\Helper;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

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

    /**
     * @param Entity\User $user
     * @param Request $request
     */
    protected function initWithFormData ($user, $request) {
        $user
            ->setName($request->get('name'))
            ->setEmail($request->get('email'))
            ->setSecret($request->get('secret'))
            ->setBirthDate($request->get('birthDate'));
    }

    /**
     * @param Request $request
     * @param $ctrlValidator
     * @return Entity\User
     * @throws BadRequest
     */
    public function insert(Request $request, $ctrlValidator)
    {
        $user = new Entity\User();

        $this->initWithFormData($user, $request);

        if ($this->countAll() == 0) {
            $user->setAdmin();
        } else {
            $user->unsetAdmin();
        }

        $user->activate();
        $user->setCreatedAt(new \DateTime('now'));

        $userValidator = new Validator\User($user);
        $userValidator->validate($this->entityManager, $ctrlValidator);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user->setHash();
        $this->entityManager->flush();

        return $user;
    }
}
