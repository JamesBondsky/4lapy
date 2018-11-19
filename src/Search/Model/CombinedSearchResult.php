<?php
    
    namespace FourPaws\Search\Model;

    
    use Doctrine\Common\Collections\ArrayCollection;
    use Elastica\Multi\ResultSet;
    use FourPaws\App\Application;
    use FourPaws\Catalog\Model\Brand;
    use FourPaws\Catalog\Model\Product;
    use FourPaws\Search\Enum\DocumentType;
    use FourPaws\Search\Factory;
    use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

    class CombinedSearchResult
    {
        /**
         * @var ArrayCollection
         */
        protected $collection;
    
        /**
         * @var ResultSet[]
         */
        private $resultSet;
    
        /**
         * @var Navigation
         */
        private $navigation;
    
        /**
         * @var string
         */
        private $query;
    
        /**
         * @var Factory
         */
        private $factory;
    
        /**
         * ProductSearchResult constructor.
         *
         * @param ResultSet $resultSet
         * @param Navigation $navigation
         * @param string $query
         *
         * @throws ServiceCircularReferenceException
         */
        public function __construct(ResultSet $resultSet, Navigation $navigation = null, $query = '')
        {
            $this->resultSet = $resultSet->getResultSets();
            $this->navigation = $navigation;
            $this->query = $query;
            $this->factory = Application::getInstance()->getContainer()->get('search.factory');
        }
    
        /**
         * @throws \RuntimeException
         * @return ArrayCollection
         */
        public function getCollection(): ArrayCollection
        {
            if (null === $this->collection) {
                $itemsList = [];
            
                /** @var \Elastica\ResultSet $item */
                foreach ($this->resultSet as $item) {
                    foreach ($item->getResults() as $result) {
                        if($result->getType() == DocumentType::PRODUCT) {
                            $itemsList[] = $this->factory->makeProductObject($result);
                        }
                        elseif ($result->getType() == DocumentType::BRAND) {
                            $itemsList[] = $this->factory->makeBrandObject($result);
                        }
                    }
                }
                
                $this->collection = new ArrayCollection($itemsList);
            }
        
            return $this->collection;
        }
    
        /**
         * @return ResultSet
         */
        public function getResultSet(): ResultSet
        {
            return $this->resultSet;
        }
    
        /**
         * @return string
         */
        public function getQuery(): string
        {
            return $this->query;
        }
    }