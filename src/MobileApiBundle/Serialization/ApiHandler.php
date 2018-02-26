<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Serialization;

use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\Interfaces\ImageInterface;
use FourPaws\MobileApiBundle\Services\Api\TextProcessor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class ApiHandler implements SubscribingHandlerInterface
{
    const FIELDS_MAP = [
        'id'       => 'ID',
        'type'     => 'PAGE_TYPE',
        'date'     => 'DATE_ACTIVE_FROM',
        'end_date' => 'DATE_ACTIVE_TO',
        'title'    => 'NAME',
        'details'  => 'PREVIEW_TEXT',
        'icon'     => 'PREVIEW_PICTURE',
        'html'     => 'DETAIL_TEXT',
        'web_url'  => 'CANONICAL_PAGE_URL',

        'subitems' => 'SUB_ITEMS',
    ];

    /**
     * @var TextProcessor
     */
    private $textProcessor;

    public function __construct(TextProcessor $textProcessor)
    {
        $this->textProcessor = $textProcessor;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_details_text',
                'method'    => 'serializeDetails',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_html_text',
                'method'    => 'serializeHtml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_image_src',
                'method'    => 'serializeImage',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_image_collection_src',
                'method'    => 'serializeImageCollection',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_info_fields',
                'method'    => 'deserializeFields',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'api_date_time',
                'method'    => 'serializeApiDateTime',
            ],
//            [
//                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
//                'format'    => 'json',
//                'type'      => 'api_date_time',
//                'method'    => 'deserializeApiDateTime',
//            ],
        ];
    }

    public function serializeApiDateTime(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        dump(123);
        die();
        if ($data === null || $data === '') {
            return $visitor->getNavigator()->accept(
                '',
                [
                    'name'   => 'string',
                    'params' => $type['params'],
                ],
                $context
            );
        }

        return $visitor->getNavigator()->accept(
            \DateTime::createFromFormat('d.m.Y', $data),
            [
                'name'   => 'DateTime',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function serializeDetails(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept(
            $this->textProcessor->processDetails($data),
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function serializeHtml(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept(
            $this->textProcessor->processHtml($data),
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function serializeImage(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /**
         * @var ImageInterface|mixed $data
         */
        $data = $data instanceof ImageInterface ? 'https://' . SITE_SERVER_NAME . $data->getSrc() : '';
        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function serializeImageCollection(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }


        if (\is_array($data)) {
            $data = array_filter($data, function ($item) {
                return $item && $item instanceof ImageInterface;
            });
            $data = array_map(function (ImageInterface $image) {
                return 'https://' . SITE_SERVER_NAME . $image->getSrc();
            }, $data);
        }

        return $visitor->getNavigator()->accept(
            \is_array($data) ? $data : [],
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function deserializeFields(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = trim($data);
        $data = explode(',', $data);
        $data = array_map(function ($item) {
            return trim($item);
        }, $data);
        $data = array_filter($data);

        $data = array_values(array_intersect_key(static::FIELDS_MAP, array_flip($data)));
        return $visitor->getNavigator()->accept(
            \is_array($data) ? $data : [],
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }
}
