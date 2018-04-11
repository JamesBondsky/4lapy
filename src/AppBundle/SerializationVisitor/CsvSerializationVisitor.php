<?php

namespace FourPaws\AppBundle\SerializationVisitor;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * Class CsvSerializationVisitor
 * @package FourPaws\AppBundle\Serialization
 */
class CsvSerializationVisitor extends AbstractVisitor
{
    private $navigator;
    private $result;
    private $delimiter = ';';
    private $data;
    private $strDelimiter = "\r\n";

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param array   $data
     * @param array   $type
     * @param Context $context
     *
     * @return mixed
     *
     * @throws NotSupportedException
     */
    public function visitArray($data, array $type, Context $context)
    {
        $res = '';
        if (\is_array($data) && !empty($data)) {
            $resData = [];
            $hasRes = false;
            foreach ($data as $key => $val) {
                if (\is_object($val)) {
                    $hasRes = true;
                    $resData[$key] = $context->getNavigator()->accept($val, $this->getElementType($type), $context);
                }
            }
            if (!$hasRes) {
                $res = implode('|', $data);
            } else {
                $res = implode($this->strDelimiter, $resData);
            }
        }
        return $res;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitNull($data, array $type, Context $context)
    {
        return '';
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitString($data, array $type, Context $context)
    {
        return $data;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type, Context $context)
    {
        return $data ? 1 : 0;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitDouble($data, array $type, Context $context)
    {
        return (string)$data;
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitInteger($data, array $type, Context $context)
    {
        return (string)$data;
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
     *
     * @return void
     */
    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $this->data = [];
    }

    /**
     * @param PropertyMetadata $metadata
     * @param mixed            $data
     *
     * @return void
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $v = $this->accessor->getValue($data, $metadata);

        $v = $this->navigator->accept($v, $metadata->type, $context);
        if ((null === $v && $context->shouldSerializeNull() !== true)
            || (true === $metadata->skipWhenEmpty && ($v instanceof \ArrayObject || is_array($v)) && 0 === count($v))
        ) {
            return;
        }

        $k = $this->namingStrategy->translateName($metadata);

        if ($metadata->inline) {
            if (\is_array($v) || ($v instanceof \ArrayObject)) {
                $this->data = array_merge($this->data, (array)$v);
            }
        } else {
            $this->data[$k] = $v;
        }
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
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
    public function getNavigator()
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
    public function setNavigator(GraphNavigator $navigator)
    {
        $this->navigator = $navigator;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->getFirstLine($this->result) . $this->strDelimiter . $this->getBody($this->result);
    }

    /**
     * @param array $res
     *
     * @return string
     */
    private function getFirstLine(array $res): string
    {
        if (\is_int(key($res))) {
            $firstLine = implode($this->delimiter, array_keys(current($res)));
        } else {
            $firstLine = implode($this->delimiter, array_keys($res));
        }

        return $firstLine;
    }

    /**
     * @param array $res
     *
     * @return string
     */
    private function getBody(array $res): string
    {
        $formattedValues = [];
        foreach ($res as $key => $val) {
            if (\is_array($val)) {
                $formattedValues[] = $this->getBody($val);
            }
        }
        if (empty($formattedValues)) {
            $formattedValues[] = implode($this->delimiter, $res);
        }
        return implode($this->strDelimiter, $formattedValues);
    }
}
