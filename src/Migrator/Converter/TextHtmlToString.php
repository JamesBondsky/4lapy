<?php

namespace FourPaws\Migrator\Converter;

use FourPaws\BitrixOrm\Type\TextContent;


/**
 * Class TextHtmlToString
 *
 * Преобразует текст/html к строке (убирая все теги)
 *
 * @package FourPaws\Migrator\Converter
 */
final class TextHtmlToString extends AbstractConverter
{
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        if (isset($data[$fieldName]['TYPE'])) {
            $text = (new TextContent($data[$fieldName]))->withType(TextContent::TYPE_HTML)->getText();
            
            $data[$fieldName] = strip_tags($text);
        } else {
            foreach ($data[$fieldName] as &$field) {
                $text  = (new TextContent($field))->withType(TextContent::TYPE_HTML)->getText();
                $field = strip_tags($text);
            }
            
            unset($field);
        }
        
        return $data;
    }
}
