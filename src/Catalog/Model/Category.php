<?php

namespace FourPaws\Catalog\Model;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Elastica\Query\AbstractQuery;
use Elastica\Query\Simple;
use Elastica\Query\Terms;
use Exception;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\IblockSection;
use FourPaws\Catalog\CatalogService;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterTrait;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;

class Category extends IblockSection implements FilterInterface
{
    use FilterTrait;

    /**
     * @var int
     */
    protected $UF_SYMLINK = 0;

    /**
     * @var Category
     */
    protected $symlink;

    /**
     * @var CatalogService
     */
    protected $catalogService;

    /**
     * @var static
     */
    protected $parent;

    /**
     * @var Collection|static[]
     */
    protected $child;

    /**
     * @var int
     */
    protected $PICTURE = 0;

    /**
     * @var FilterCollection
     */
    private $filterList;

    /**
     * Category constructor.
     *
     * @param array $fields
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        $this->catalogService = Application::getInstance()->getContainer()->get('catalog.service');
        //По умолчанию фильтр по категории невидим.
        $this->setVisible(false);
        $this->child = new ArrayCollection();
    }

    /**
     * @param array $fields
     *
     * @throws IblockNotFoundException
     * @return Category
     */
    public static function createRoot(array $fields = [])
    {
        $category = new self(
            array_merge(
                $fields,
                [
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    'ID'        => 0,
                    'CODE'      => '',
                    'NAME'      => 'Поиск товаров',
                ]
            )
        );

        $category->setVisible(true);

        return $category;
    }

    /**
     * @return int
     */
    public function getPictureId(): int
    {
        return (int)$this->PICTURE;
    }

    /**
     * @param int $pictureId
     * @return static
     */
    public function setPictureId(int $pictureId)
    {
        $this->PICTURE = $pictureId;
        return $this;
    }

    /**
     * @return null|static
     */
    public function getParent()
    {
        if (null === $this->parent) {
            if (1 === $this->getDepthLevel()) {
                $parent = (new static())
                    ->withId(0)
                    ->withName(static::ROOT_SECTION_NAME)
                    ->withCode(static::ROOT_SECTION_CODE)
                    ->withSectionPageUrl('/catalog/')
                    ->withListPageUrl('/catalog/');
                $this->withParent($parent);
            }
            if ($this->getIblockSectionId()) {
                $parent = (new CategoryQuery())
                    ->withFilterParameter('ID', $this->getIblockSectionId())
                    ->exec()
                    ->first();
                $this->withParent($parent);
            }
        }
        return $this->parent;
    }

    /**
     * @param Category $parent
     *
     * @return Category
     */
    public function withParent(Category $parent): Category
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param bool $hasElements
     *
     * @return Category[]|Collection
     */
    public function getChild(bool $hasElements = true): Collection
    {
        if (null === $this->child) {
            $this->child = new ArrayCollection();
        }

        if (0 === $this->child->count() && $this->getRightMargin() - $this->getLeftMargin() > 1) {
            $this->child = (new CategoryQuery())
                ->withFilterParameter('SECTION_ID', $this->getId())
                ->withFilterParameter('CNT_ACTIVE', 'Y')
                ->withOrder(['SORT' => 'ASC'])
                ->withCountElements(true)
                ->exec();
            $this->child->map(function (Category $category) {
                $category->withParent($this);
            });
        }

        $result = $this->child;
        if ($hasElements) {
            $result = $result->filter(function (Category $category) {
                return $category->getElementCount() > 0;
            });
        }

        return $result;
    }

    public function withChild(Collection $collection)
    {
        $collection = $collection->filter(function ($value) {
            return $value instanceof static;
        });
        $this->child = $collection;
    }

    /**
     * Возвращает ссылку на категорию с подменой, если используется связка с другой категорией.
     *
     * @return string
     */
    public function getSectionPageUrl(): string
    {
        $symlink = $this->getSymlink();

        if ($symlink instanceof Category) {
            return $symlink->getSectionPageUrl();
        }

        return parent::getSectionPageUrl();
    }

    /**
     * @return Category
     */
    public function getSymlink()
    {
        if (is_null($this->symlink)) {
            /**
             * Обязательно запрашивается активный раздел, т.к. на него будет ссылка
             * и при деактивации целевого раздела показывать битую ссылку плохо.
             */
            $this->symlink = (new CategoryQuery())->withFilterParameter('=ID', (int)$this->UF_SYMLINK)
                ->exec()
                ->current();
        }

        return $this->symlink;
    }

    /**
     * @throws Exception
     * @return FilterCollection
     */
    public function getFilters(): FilterCollection
    {
        /**
         * Обязательно надо хранить актуальную коллекцию фильтров,
         * т.к. её состояние в процессе поиска товаров будет меняться.
         */
        if (is_null($this->filterList)) {
            $this->filterList = $this->catalogService->getFilters($this);
        }

        return $this->filterList;
    }

    /**
     * @inheritdoc
     */
    public function doGetAllVariants(): VariantCollection
    {
        $doGetAllVariants = function () {
            $categoryQuery = new CategoryQuery();

            //Если это не корневой раздел
            if ($this->getId() > 0) {
                $categoryQuery->withFilterParameter('LEFT_MARGIN', $this->getLeftMargin())
                    ->withFilterParameter('RIGHT_MARGIN', $this->getRightMargin());
            }

            $categoryCollection = $categoryQuery->withOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec();

            $variants = [];

            /** @var Category $category */
            foreach ($categoryCollection as $category) {
                //TODO Добавить к варианту уровень вложенности, чтобы отобразить дерево, когда нужно.

                /**
                 * Все варианты по-умолчанию выбраны,
                 * т.к. мы по умолчанию ищем по разделу и всем подразделам
                 */
                $variants[] = (new Variant())->withName($category->getName())
                    ->withValue($category->getId())
                    ->withChecked(true);
            }

            return $variants;
        };

        /** @var Variant[] $variants */
        $variants = (new BitrixCache())->withId(__METHOD__ . $this->getId())
            ->withIblockTag($this->getIblockId())
            ->resultOf($doGetAllVariants);

        return new VariantCollection($variants);
    }

    /**
     * @inheritdoc
     */
    public function getFilterRule(): AbstractQuery
    {
        /**
         * Если корневая категория, то фильтра по ней не будет
         */
        if ($this->getId() <= 0 || $this->getIblockId() <= 0) {
            return new Simple([]);
        }

        //TODO Возможно, придётся переделать тут, когда будет реализовываться древовидный фильтр по категориям

        $sectionIdList = [];

        /** @var Variant $variant */
        foreach ($this->getAllVariants() as $variant) {
            $sectionIdList[] = (int)$variant->getValue();
        }

        if (empty($sectionIdList)) {
            return new Simple([]);
        }

        return new Terms($this->getRuleCode(), $sectionIdList);
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'sectionIdList';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Category';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        /**
         * Категория не связана ни с каким свойством
         */
        return '';
    }

    /**
     * @param Request $request
     */
    public function initState(Request $request)
    {
        // TODO: Implement initState() method для древовидного фильтра.
    }
}
