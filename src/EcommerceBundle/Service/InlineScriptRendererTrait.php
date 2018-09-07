<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.09.18
 * Time: 17:48
 */

namespace FourPaws\EcommerceBundle\Service;


use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Trait InlineScriptRendererTrait
 *
 * @package FourPaws\EcommerceBundle\Service
 */
trait InlineScriptRendererTrait
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var EngineInterface
     */
    protected $renderer;

    /**
     * InlineScriptRendererTrait constructor.
     *
     * @param SerializerInterface $serializer
     * @param EngineInterface     $renderer
     */
    public function __construct(SerializerInterface $serializer, EngineInterface $renderer)
    {
        $this->serializer = $serializer;
        $this->renderer = $renderer;
    }

    /**
     * @param object $data
     * @param bool   $addScriptTag
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderScript($data, bool $addScriptTag = false): string
    {
        /** @noinspection PhpParamsInspection */
        $data = $this->serializer->serialize($data, 'json');

        return \trim($this->renderer->render('EcommerceBundle:Scripts:inline.script.php', \compact('data', 'addScriptTag')));
    }

    /**
     * @param object $data
     * @param string $presetName
     * @param bool   $addScriptTag
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderPreset($data, string $presetName, bool $addScriptTag): string
    {
        /** @noinspection PhpParamsInspection */
        $data = $this->serializer->serialize($data, 'json');

        return \trim($this->renderer->render('EcommerceBundle:Scripts:preset.inline.script.php', \compact('data', 'presetName', 'addScriptTag')));
    }
}
