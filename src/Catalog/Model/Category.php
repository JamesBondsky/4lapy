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
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterTrait;
use FourPaws\Catalog\Model\Filter\FilterInterface;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Service\FilterService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class Category
 *
 * @package FourPaws\Catalog\Model
 */
class Category extends IblockSection implements FilterInterface
{
    public const UNSORTED_CATEGORY_CODE = 'unsorted';

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
     * @var static
     */
    protected $parent;
    /**
     * It`s root category
     *
     * @var bool
     */
    protected $root = false;
    /**
     * @var Collection|static[]
     */
    protected $child;
    /**
     * @var int
     */
    protected $PICTURE = 0;
    /**
     * @var string
     */
    protected $UF_DISPLAY_NAME = '';
    /**
     * @var string
     */
    protected $UF_SUFFIX = '';
    /**
     * @var bool
     */
    protected $UF_LANDING;
    /**
     * @var bool
     */
    protected $UF_DEF_FOR_LANDING;
    /**
     * @var string
     */
    protected $UF_LANDING_BANNER;
    protected $UF_LANDING_BANNER2;
    /** @var string */
    protected $UF_FAQ_SECTION;
    /** @var string */
    protected $UF_FORM_TEMPLATE;
    /** @var string */
    protected $UF_SUB_DOMAIN;
    /** @var bool */
    protected $UF_SHOW_FITTING = false;
    /** @var bool */
    protected $UF_LANDING_ARTICLES = false;
    /** @var array */
    protected $UF_RECOMMENDED;
    /** @var bool */
    protected $UF_SKIP_AUTOSORT = false;
    /**
     * @var FilterCollection
     */
    private $filterList;
    /**
     * @var bool
     */
    protected $activeLandingCategory = false;

    /**
     * Category constructor.
     *
     * @param array $fields
     *
     * @throws ServiceCircularReferenceException
     */
    public function __construct(array $fields = [])
    {
        parent::__construct($fields);
        //По умолчанию фильтр по категории невидим.
        $this->setVisible(false);
        $this->child = new ArrayCollection();
    }

    /**
     * @param array $fields
     *
     * @throws IblockNotFoundException
     * @return Category
     * @throws ServiceCircularReferenceException
     */
    public static function createRoot(array $fields = []): Category
    {
        $category = new self(
            array_merge(
                ['NAME' => 'Результаты поиска',],
                $fields,
                [
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                    'ID'        => 0,
                    'CODE'      => '',
                ]
            )
        );

        $category->setVisible(true);
        $category->setRoot(true);

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
     *
     * @return static
     */
    public function setPictureId(int $pictureId)
    {
        $this->PICTURE = $pictureId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return (string)$this->UF_DISPLAY_NAME;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function withDisplayName($name)
    {
        $this->UF_DISPLAY_NAME = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return (string)$this->UF_SUFFIX;
    }

    /**
     * @param $suffix
     *
     * @return $this
     */
    public function withSuffix($suffix)
    {
        $this->UF_SUFFIX = $suffix;

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
                    ->withFilterParameter('=ID', $this->getIblockSectionId())
                    ->withoutFilterParameter('ACTIVE')
                    ->exec()
                    ->first();

                $this->withParent($parent);
            }
        }

        return $this->parent;
    }

    /**
     * @return CategoryCollection
     */
    public function getFullPathCollection(): CategoryCollection
    {
        $collection = new CategoryCollection(new \CDBResult());
        $section = $this;

        while ($section) {
            $collection->add($section);

            if (!$section->getIblockSectionId()) {
                break;
            }

            $section = $section->getParent();
        }

        return $collection;
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

        if (0 === $this->child->count()) {
            if ($this->getRightMargin() - $this->getLeftMargin() > 1) {
                $this->child = (new CategoryQuery())
                    ->withFilterParameter('=SECTION_ID', $this->getId())
                    ->withFilterParameter('CNT_ACTIVE', 'Y')
                    ->withOrder(['SORT' => 'ASC'])
                    ->withCountElements(true)
                    ->exec();
                $this->child->map(function (Category $category) {
                    $category->withParent($this);
                });
            } elseif ($this->isRoot()) {
                $this->child = (new CategoryQuery())
                    ->withFilterParameter('SECTION_ID', false)
                    ->withFilterParameter('CNT_ACTIVE', 'Y')
                    ->withOrder(['SORT' => 'ASC'])
                    ->withCountElements(true)
                    ->exec();
            }
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
        /** поставленна базовая защита на дурака
         * если будет выжирать вся память, то мы вошли в рекурсию, через каскад разделов
         */
        if ($symlink !== null) {
            return $symlink->getSectionPageUrl();
        }

        return parent::getSectionPageUrl();
    }

    /**
     * @return Category|null
     */
    public function getSymlink(): ?Category
    {
        if ($this->symlink === null && (int)$this->UF_SYMLINK > 0 && (int)$this->UF_SYMLINK !== $this->getId()) {
            /**
             * Обязательно запрашивается активный раздел, т.к. на него будет ссылка
             * и при деактивации целевого раздела показывать битую ссылку плохо.
             */
            $res = (new CategoryQuery())->withFilterParameter('=ID', (int)$this->UF_SYMLINK)
                                        ->exec();
            $this->symlink = $res->isEmpty() ? null : $res->current();
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
        if (null === $this->filterList) {
            $this->filterList = Application::getInstance()
                                           ->getContainer()
                                           ->get(FilterService::class)->getCategoryFilters($this);
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

    public function getCanonicalName()
    {
        $suffix = '';
        if ($this->getParent()) {
            $suffix = $this->getParent()
                           ->getSuffix();
        }

        return $this->getDisplayName() ?: trim(implode(' ', [
            $this->getName(),
            $suffix
        ]));
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

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->root;
    }

    /**
     * @param bool $root
     */
    public function setRoot(bool $root)
    {
        $this->root = $root;
    }

    /**
     * @return bool
     */
    public function isLanding(): bool
    {
        return \in_array($this->getUfLanding(), [
            'Y',
            '1',
            1,
            true
        ], true);
    }

    public function getUfLanding()
    {
        return $this->UF_LANDING;
    }

    /**
     * @param string|bool|int $ufLanding
     */
    public function setUfLanding($ufLanding): void
    {
        $this->UF_LANDING = $ufLanding;
    }

    /**
     * @return mixed
     */
    public function getUfLandingBanner()
    {
        return $this->UF_LANDING_BANNER;
    }

    /**
     * @param mixed $ufLandingBanner
     */
    public function setUfLandingBanner($ufLandingBanner): void
    {
        $this->UF_LANDING_BANNER = $ufLandingBanner;
    }

    /**
     * @return mixed
     */
    public function getUfLandingBanner2()
    {
        return $this->UF_LANDING_BANNER2;
    }

    /**
     * @param mixed $ufLandingBanner
     */
    public function setUfLandingBanner2($ufLandingBanner): void
    {
        $this->UF_LANDING_BANNER2 = $ufLandingBanner;
    }

    /**
     * @return mixed
     */
    public function getUfFaqSection()
    {
        return $this->UF_FAQ_SECTION;
    }

    /**
     * @param mixed $ufFaqSection
     */
    public function setUfFaqSection($ufFaqSection): void
    {
        $this->UF_FAQ_SECTION = $ufFaqSection;
    }

    /**
     * @return bool
     */
    public function isDefForLanding(): bool
    {
        return $this->UF_DEF_FOR_LANDING;
    }

    /**
     * @param bool $defForLanding
     *
     * @return Category
     */
    public function setDefForLanding(bool $defForLanding): self
    {
        $this->UF_DEF_FOR_LANDING = $defForLanding;

        return $this;
    }

    /**
     * @param bool $withParent
     *
     * @return string
     */
    public function getFormTemplate(bool $withParent = true): ?string
    {
        if ($withParent && !$this->UF_FORM_TEMPLATE) {
            /** @var Category $parent */
            $parent = $this->findFromParent(function (Category $category) {
                return (bool)$category->getFormTemplate(false);
            });

            $this->UF_FORM_TEMPLATE = $parent ? $parent->getFormTemplate(false) : '';
        }

        return $this->UF_FORM_TEMPLATE;
    }

    /**
     * @param string $formTemplate
     *
     * @return Category
     */
    public function setFormTemplate(string $formTemplate): self
    {
        $this->UF_FORM_TEMPLATE = $formTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubDomain(): string
    {
        return $this->UF_SUB_DOMAIN;
    }

    /**
     * @param string $subDomain
     *
     * @return Category
     */
    public function setSubDomain(string $subDomain): self
    {
        $this->UF_SUB_DOMAIN = $subDomain;

        return $this;
    }

    /**
     * @return int
     */
    public function getLandingArticlesSectionId(): int
    {
        return $this->UF_LANDING_ARTICLES ?? -1;
    }

    /**
     * @param int $articlesSectionId
     *
     * @return Category
     */
    public function setLandingArticlesSectionId(int $articlesSectionId): self
    {
        $this->UF_LANDING_ARTICLES = $articlesSectionId;

        return $this;
    }

    /**
     * @param bool $withParent
     *
     * @return bool
     */
    public function isShowFitting($withParent = true): bool
    {
        if ($withParent && !$this->UF_SHOW_FITTING) {
            /** @var Category $parent */
            $parent = $this->findFromParent(function (Category $category) {
                return $category->isShowFitting(false);
            });

            $this->UF_SHOW_FITTING = $parent !== false;
        }

        return $this->UF_SHOW_FITTING;
    }

    /**
     * @param bool $isShowFitting
     *
     * @return Category
     */
    public function setShowFitting(bool $isShowFitting): self
    {
        $this->UF_SHOW_FITTING = $isShowFitting;

        return $this;
    }

    /**
     * @param bool $withParent
     *
     * @return array|int[]
     */
    public function getRecommendedProductIds($withParent = true): array
    {
        if ($withParent && !$this->UF_RECOMMENDED) {
            /** @var Category $parent */
            $parent = $this->findFromParent(function (Category $category) {
                return \count($category->getRecommendedProductIds(false)) > 0;
            });

            $this->UF_RECOMMENDED = $parent ? $parent->getRecommendedProductIds(false) : [];
        }

        return $this->UF_RECOMMENDED ?: [];
    }

    /**
     * @param array|int[] $recommendedProductIds
     *
     * @return Category
     */
    public function setRecommendedProductIds(array $recommendedProductIds): self
    {
        $this->UF_RECOMMENDED = $recommendedProductIds;

        return $this;
    }

    public function isSkipAutosort(): bool
    {
        return $this->UF_SKIP_AUTOSORT;
    }

    /**
     * @param bool $skipAutosort
     *
     * @return Category
     */
    public function setSkipAutosort(bool $skipAutosort): self
    {
        $this->UF_SKIP_AUTOSORT = $skipAutosort;

        return $this;
    }

    /**
     * @param callable $find
     *
     * @return Collection
     */
    protected function findFromParent(callable $find)
    {
        return $this->getFullPathCollection()->filter($find)->last();
    }

    /**
     * @return bool
     */
    public function isActiveLandingCategory(): bool
    {
        return $this->activeLandingCategory;
    }

    /**
     * @param bool $activeLandingCategory
     *
     * @return $this
     */
    public function setActiveLandingCategory(bool $activeLandingCategory): self
    {
        $this->activeLandingCategory = $activeLandingCategory;

        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'UF_SYMLINK',
            'symlink',
            'parent',
            'root',
            'child',
            'PICTURE',
            'UF_DISPLAY_NAME',
            'UF_SUFFIX',
            'UF_LANDING',
            'UF_DEF_FOR_LANDING',
            'UF_LANDING_BANNER',
            'UF_LANDING_BANNER2',
            'UF_FAQ_SECTION',
            'UF_FORM_TEMPLATE',
            'UF_SUB_DOMAIN',
            'UF_SHOW_FITTING',
            'UF_LANDING_ARTICLES',
            'UF_RECOMMENDED',
            'IBLOCK_ID',
            'ID',
            'SORT',
            'DEPTH_LEVEL',
            'LEFT_MARGIN',
            'RIGHT_MARGIN',
            'SECTION_PAGE_URL',
            'IBLOCK_SECTION_ID',
            'ELEMENT_CNT',
            'NAME',
            'CODE',
            'XML_ID'
        ];
    }
}
