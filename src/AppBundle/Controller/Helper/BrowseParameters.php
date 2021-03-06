<?php

namespace AppBundle\Controller\Helper;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Helper;

class BrowseParameters
{

    const MIN_LIMIT = 1;
    const DEFAULT_LIMIT = 10;
    const DEFAULT_OFFSET = 0;

    /** @var String */
    private $classAlias;
    /** @var Integer */
    private $limit;
    /** @var  Integer */
    private $offset;
    /** @var  String */
    private $orderBy;
    /** @var  String */
    private $direction;
    /** @var int */
    private $count;
    /** @var int|null */
    private $currentPage;
    /** @var int|null */
    private $totalPages;
    /** String[] */
    private $keywords;
    /** String[] */
    private $validOrderColumns;

    /**
     * @param string $classAlias
     * @param array $validOrderColumns
     */
    function __construct($classAlias = null, $validOrderColumns = null)
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();

        $this->setLimit((int) $request->query->get('limit'));
        $this->setCurrentPage((int)$request->query->get('page'));
        $this->setOffset((int)$request->query->get('offset'));

        $this->setClassAlias($classAlias);
        $this->setValidOrderColumns($validOrderColumns);
        $this->setOrderBy($request->query->get('orderby'));

        $this->setAscending(!(bool) $request->query->get('desc'));

        $this->setKeywords($request->query->get('keywords'));
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
     * @return BrowseParameters
     */
    public function setLimit($limit)
    {
        $this->limit = $limit ?: self::DEFAULT_LIMIT;

        if ($this->limit <= self::MIN_LIMIT) {
            $this->limit = self::DEFAULT_LIMIT;
        }

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
     * @param int|null $offset
     * @return BrowseParameters
     */
    public function setOffset($offset = null)
    {
        if (!empty($this->currentPage) && $this->currentPage > 0) {
            $this->offset = ($this->currentPage - 1) * $this->limit;
        } else {
            $this->offset = $offset;
            $this->currentPage = null;

            if (empty($this->offset) || $this->offset < 0) {
                $this->offset = 0;
            }
        }

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
     * @return BrowseParameters
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = strtolower($orderBy);

        if (!empty($this->validOrderColumns)) {
            $match = false;

            foreach ($this->validOrderColumns as $validOrderColumn) {
                if ($this->orderBy == strtolower($validOrderColumn)) {
                    $match = true;
                }
            }

            if (empty($match)) {
                $this->orderBy = $this->validOrderColumns[0];
            }
        }

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
     * @return BrowseParameters
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @param bool $ascending
     * @return BrowseParameters
     */
    public function setAscending($ascending)
    {
        $this->direction = $ascending ? 'ASC' : 'DESC';
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     * @return BrowseParameters
     */
    public function setCount($count)
    {
        $this->count = $count;

        if ($count >= 0 && !empty($this->currentPage)) {
            $this->totalPages = ceil($this->count / $this->limit);
        }

        if ($this->currentPage > $this->totalPages) {
            $this->setCurrentPage($this->totalPages);
            $this->setOffset();
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int|null $currentPage
     * @return BrowseParameters
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param String $keywords
     * @return BrowseParameters
     */
    public function setKeywords($keywords)
    {
        $this->keywords = array();

        foreach (explode(' ', $keywords) as $keyword) {
            $keyword = trim($keyword);

            if (!empty($keyword)) {
                $this->keywords[] = $keyword;
            }
        }

        return $this;
    }

    /**
     * @return String
     */
    public function getClassAlias()
    {
        return $this->classAlias;
    }

    /**
     * @param String $classAlias
     * @return BrowseParameters
     */
    public function setClassAlias($classAlias)
    {
        $this->classAlias = $classAlias;
        return $this;
    }

    /**
     * @return String[]
     */
    protected function getValidOrderColumns()
    {
        return $this->validOrderColumns;
    }

    /**
     * @param String[] $validOrderColumns
     */
    protected function setValidOrderColumns($validOrderColumns)
    {
        $this->validOrderColumns = $validOrderColumns;
    }

    private function getMetadataBrowsingLinks() {
        $links = array();

        if ($this->getCurrentPage() > 1) {
            $links['firstPage'] = Helper\HttpServerVars::getLinkToPage(1);

            $links['previousPage'] = Helper\HttpServerVars::getLinkToPage(
                $this->getCurrentPage() - 1
            );
        }

        if ($this->getCurrentPage() < $this->getTotalPages()) {
            $links['nextPage'] = Helper\HttpServerVars::getLinkToPage(
                $this->getCurrentPage() + 1
            );

            $links['lastPage'] = Helper\HttpServerVars::getLinkToPage(
                $this->getTotalPages()
            );
        }

        return $links;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $metadata = array(
            'limit'     => $this->getLimit(),
            'offset'    => $this->getOffset(),
            'orderBy'   => $this->getOrderBy(),
            'direction' => $this->getDirection()
        );

        if ($this->getCurrentPage()) {
            $metadata['page'] = $this->getCurrentPage();
        }

        if ($this->getCount() >= 0) {
            $metadata['count'] = $this->getCount();
        }
        if (!empty($this->getCurrentPage())) {
            $metadata['totalPages']  = $this->getTotalPages();
        }

        if ($this->getKeywords()) {
            $metadata['keywords'] = $this->getKeywords();
        }

        $metadata = array_merge($metadata, $this->getMetadataBrowsingLinks());

        return $metadata;
    }
}
