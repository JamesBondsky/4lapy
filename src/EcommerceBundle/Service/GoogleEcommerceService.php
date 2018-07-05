<?php

namespace FourPaws\EcommerceBundle\Service;


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
class GoogleEcommerceService
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
     * GoogleEcommerceService constructor.
     *
     * @param SerializerInterface $serializer
     * @param EngineInterface $renderer
     */
    public function __construct(SerializerInterface $serializer, EngineInterface $renderer)
    {
        $this->serializer = $serializer;
        $this->renderer = $renderer;
    }

    /**
     * @param string $data
     * @param bool $addScriptTag
     *
     * @throws RuntimeException
     */
    protected function renderScript(string $data, bool $addScriptTag): void
    {
        $this->renderer->render('EcommerceBundle:GoogleEcommerce:inline.script.php', \compact($data, $addScriptTag));
    }
}
