<?php

namespace AppBundle\Controller\Helper;

use Symfony\Component\HttpFoundation\Request;

class PagingParameters
{

    const MIN_LIMIT      = 1;
    const DEFAULT_LIMIT  = 10;
    const DEFAULT_OFFSET = 0;

    /** @var  Integer */
    private $limit;
    /** @var  Integer */
    private $offset;
    /** @var  String */
    private $orderBy;
    /** @var  String */
    private $direction;

    /**
     * @param array $validOrderColumns
     */
    function __construct($validOrderColumns = null)
    {
        $request  = Request::createFromGlobals();
        $request->getPathInfo();

        $this->limit = (int) $request->query->get('limit') ?: self::DEFAULT_LIMIT;

        if ($this->limit <= self::MIN_LIMIT) {
            $this->limit = self::DEFAULT_LIMIT;
        }

        $page = (int) $request->query->get('page');

        if (!empty($page) && $page > 0) {
            $this->offset = ($page - 1) * $this->limit;
        } else {
            $this->offset = (int) $request->query->get('offset');

            if (empty($this->offset) || $this->offset < 0) {
                $this->offset = 0;
            }
        }

        $this->orderBy = strtolower($request->query->get('orderby'));

        if (!empty($validOrderColumns)) {
            $match   = false;

            foreach ($validOrderColumns as $validOrderColumn) {
                if ($this->orderBy == strtolower($validOrderColumn)) {
                    $match = true;
                }
            }

            if (!$match) {
                $this->orderBy = $validOrderColumns[0];
            }
        }

        $this->direction = $request->query->get('desc') ? 'DESC' : 'ASC';
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return PagingParameters
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return PagingParameters
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return String
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param String $orderBy
     * @return PagingParameters
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @return String
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param String $direction
     * @return PagingParameters
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }


}