<?php

namespace FourPaws\BitrixOrm\Type;

/**
 * Class TextContent
 *
 * @package FourPaws\BitrixOrm\Type
 */
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

    /**
     * TextContent constructor.
     *
     * @param array|null $fields
     */
    public function __construct(?array $fields = null)
    {
        if (null !== $fields && isset($fields['TYPE'], $fields['TEXT'])) {
            $this->withType($fields['TYPE'])
                ->withText($fields['TEXT']);
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
            return \html_entity_decode($this->text);
        }

        return $this->text;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    private function matchType($type): bool
    {
        return \strtolower($this->getType()) === $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
