<?php

namespace FourPaws\BitrixIblockORM\Model;

class TextContent
{
    const TYPE_HTML = 'html';

    const TYPE_TEXT = 'text';

    /**
     * @var string Тип содержимого
     * @see TextContent::TYPE_*
     */
    private $type = self::TYPE_HTML;

    /**
     * @var string
     */
    private $text = '';

    public function __construct($fields = null)
    {
        if (is_array($fields) && isset($fields['TYPE'], $fields['TEXT'])) {

            $this->withType($fields['TYPE'])
                 ->withText($fields['TEXT']);

        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return TextContent
     */
    public function withType(string $type): TextContent
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        if ($this->matchType(self::TYPE_HTML)) {
            return html_entity_decode($this->text);
        } else {
            return $this->text;
        }
    }

    /**
     * @param string $text
     *
     * @return TextContent
     */
    public function withText(string $text): TextContent
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    private function matchType($type): bool
    {
        return strtolower($this->getType()) == $type;
    }
}
