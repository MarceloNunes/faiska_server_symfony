<?php

namespace AppBundle\Repository;

use AppBundle\Controller\Helper\BrowseParameters;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

abstract class BaseRepository
{
    /** @var string  */
    protected $className;
    /** @var string  */
    protected $classAlias;
    /** @var array  */
    protected $orderColumns;
    /** @var EntityManagerInterface  */
    protected $entityManager;

    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public abstract function __construct(EntityManagerInterface $entityManager);

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return BaseRepository
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassAlias()
    {
        return $this->classAlias;
    }

    /**
     * @param string $classAlias
     * @return BaseRepository
     */
    public function setClassAlias($classAlias)
    {
        $this->classAlias = $classAlias;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderColumns()
    {
        return $this->orderColumns;
    }

    /**
     * @param array $orderColumns
     * @return BaseRepository
     */
    public function setOrderColumns($orderColumns)
    {
        $this->orderColumns = $orderColumns;
        return $this;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return BaseRepository
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @param BrowseParameters $parameters
     * @return QueryBuilder
     */
    protected function setupBrowseQueryBuilder(BrowseParameters $parameters)
    {
        return $this->entityManager->createQueryBuilder()
            ->select($this->classAlias)
            ->from($this->className, $this->classAlias)
            ->setMaxResults($parameters->getLimit())
            ->setFirstResult($parameters->getOffset())
            ->orderBy(
                $this->classAlias.'.'.$parameters->getOrderBy(),
                $parameters->getDirection()
            );
    }

    /**
     * @param BrowseParameters $parameters
     * @return QueryBuilder
     */
    protected function setupCountQueryBuilder(BrowseParameters $parameters)
    {
        return $this->entityManager->createQueryBuilder()
            ->select('count('.$this->classAlias.')')
            ->from($this->className, $this->classAlias);
    }
}
