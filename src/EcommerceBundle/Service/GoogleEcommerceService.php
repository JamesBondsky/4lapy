<?php

namespace FourPaws\EcommerceBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Ecommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\GoogleEcommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Promotion;
use FourPaws\EcommerceBundle\Exception\InvalidArgumentException;
use FourPaws\EcommerceBundle\Utils\ArrayMapper;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class GoogleEcommerceService
 *
 * @todo добавить общий класс/trait; добавить настройки с магией
 *
 * @package FourPaws\EcommerceBundle\Service
 */
class GoogleEcommerceService implements ScriptRenderedInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var EngineInterface
     */
    private $renderer;
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * GoogleEcommerceService constructor.
     *
     * @param SerializerInterface $serializer
     * @param ArrayTransformerInterface $arrayTransformer
     * @param EngineInterface $renderer
     */
    public function __construct(SerializerInterface $serializer, ArrayTransformerInterface $arrayTransformer, EngineInterface $renderer)
    {
        $this->serializer = $serializer;
        $this->renderer = $renderer;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param string $data
     * @param bool $addScriptTag
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderScript($data, bool $addScriptTag): string
    {
        /** @noinspection PhpParamsInspection */
        $data = $this->serializer->serialize($data, 'json');

        return \trim($this->renderer->render('EcommerceBundle:GoogleEcommerce:inline.script.php', \compact('data', 'addScriptTag')));
    }

    /**
     * @param ArrayMapper $mapper
     * @param array $data
     * @param string $type
     *
     * @return GoogleEcommerce
     *
     * @throws InvalidArgumentException
     */
    public function buildPromotionFromArray(ArrayMapper $mapper, array $data, string $type): GoogleEcommerce
    {
        $ecommerce = (new GoogleEcommerce())->setEcommerce(new Ecommerce());

        $promotions = \array_map(function ($promotion) {
            return $this->arrayTransformer->fromArray($promotion, Promotion::class);
        }, $mapper->mapCollection($data));

        try {
            $ecommerce->getEcommerce()->{$type} = (new Action())->setPromotions(new ArrayCollection($promotions));
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported ecommerce type %s',
                $type
            ));
        }

        return $ecommerce;
    }

    /**
     * Array mapper factory method
     *
     * @param array $map
     *
     * @return ArrayMapper
     */
    public function getArrayMapper(array $map): ArrayMapper
    {
        return new ArrayMapper($map);
    }
}
