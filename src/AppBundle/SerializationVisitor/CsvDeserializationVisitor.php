<?php

namespace FourPaws\AppBundle\SerializationVisitor;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\NullAwareVisitorInterface;
use phpDocumentor\Reflection\Types\Scalar;

/**
 * Class CsvSerializationVisitor
 * @package FourPaws\AppBundle\Serialization
 */
class CsvDeserializationVisitor extends AbstractVisitor implements NullAwareVisitorInterface
{

    private $result;
    private $navigator;
    private $delimiter = ';';
    private $strDelimiter = "\r\n";
    private $data;

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param string  $data
     * @param array   $type
     * @param Context $context
     *
     * @return array
     *
     * @throws NotSupportedException
     */
    public function visitArray($data, array $type, Context $context): array
    {
        $res = [];
        if (!empty($data)) {
            $newRes = [];
            if(\is_array($data)) {
                foreach ($data as $item) {
                    $newRes[] = $context->getNavigator()->accept($item, $this->getElementType($type), $context);
                }
                $res = $newRes;
            }

            if(empty($newRes)) {
                $res = explode('|', $data);
            }
        }
        return $res;
    }

    /**
     * Determine if a value conveys a null value.
     * An example could be an xml element (Dom, SimpleXml, ...) that is tagged with a xsi:nil attribute
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isNull($value): bool
    {
        return $value === '';
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitNull($data, array $type, Context $context)
    {
        return null;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitString($data, array $type, Context $context)
    {
        return $data;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type, Context $context)
    {
        return (int)$data === 1;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitDouble($data, array $type, Context $context)
    {
        return (double)$data;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitInteger($data, array $type, Context $context)
    {
        return (int)$data;
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
     *
     * @param Context       $context
     *
     * @return void
     */
    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context): void
    {
        $this->data = new $metadata->name();
    }

    /**
     * @param PropertyMetadata $metadata
     * @param mixed            $data
     *
     * @param Context          $context
     *
     * @return void
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context): void
    {
        $k = $this->namingStrategy->translateName($metadata);
        $v = $data[$k];

        $v = $this->navigator->accept($v, $metadata->type, $context);
        if ((null === $v && $context->shouldSerializeNull() !== true)
            || (true === $metadata->skipWhenEmpty && ($v instanceof \ArrayObject || \is_array($v)) && 0 === \count($v))
        ) {
            return;
        }

        /** установка занчений класса */
        $this->accessor->setValue($this->data, $v, $metadata);
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
     *
     * @param Context       $context
     *
     * @return mixed
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $this->result[] = $this->data;
    }

    /**
     * @deprecated use Context::getNavigator/Context::accept instead
     * @return GraphNavigator
     */
    public function getNavigator(): GraphNavigator
    {
        return $this->navigator;
    }

    /**
     * Called before serialization/deserialization starts.
     *
     * @param GraphNavigator $navigator
     *
     * @return void
     */
    public function setNavigator(GraphNavigator $navigator): void
    {
        $this->navigator = $navigator;
    }

    /**
     * @return object|array|scalar
     */
    public function getResult()
    {
        if(\count($this->result) === 1){
            $this->result = current($this->result);
        }
        return $this->result;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    public function prepare($data): array
    {
        $explode = explode($this->strDelimiter, $data);
        $header = explode($this->delimiter, $explode[0]);
        $res = [];
        foreach ($explode as $key => $val) {
            if ($key > 0) {
                $explode = explode($this->delimiter, $val);
                $fields = [];
                foreach ($explode as $headerKey => $fieldVal) {
                    $fields[$header[$headerKey]] = $fieldVal;
                }
                $res[] = $fields;
            }
        }
        if (\count($res) === 1) {
            $res = current($res);
        }
        return $res;
    }
}
