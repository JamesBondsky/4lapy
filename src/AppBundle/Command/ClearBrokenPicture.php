<?php

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Loader;


use Psr\Log\LoggerAwareInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearBrokenPicture
 *
 * Специфичная для задачи команда
 *
 * @package FourPaws\AppBundle\Command
 */
class ClearBrokenPicture extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @throws InvalidArgumentException
     */
    public function configure()
    {
        $this->setName('fourpaws:specific:clearbrokenpicture')->setDescription('Clear users');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            Loader::includeModule('iblock');
            $this->removePictures();

            $this->log()->info(sprintf('Broken entities has been delete.'));
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Unknown error: %s', $e->getMessage()));
        }

        return null;
    }

    /**
     * @return array
     */
    public function getElementList(): array
    {
        $elementList = [];

        $elementCollection = \CIBlockElement::GetList(
            ['rand' => 'asc'],
            [
                '=PROPERTY_IMG' => '1',
                'IBLOCK_ID'     => 3,
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_IMG',
            ]
        );

        $this->log()->info(sprintf('Full count %u', $elementCollection->SelectedRowsCount()));

        while ($element = $elementCollection->Fetch()) {
            $elementList[] = $element;
        }

        return $elementList;
    }

    /**
     *
     */
    private function removePictures()
    {
        $elementList = $this->getElementList();

        foreach ($elementList as $element) {
            $this->removePicture($element);
        }

        $this->log()->info('Done');
    }

    /**
     * @param array $element
     */
    private function removePicture(array $element)
    {
        $position = array_search('1', $element['PROPERTY_IMG_VALUE'], false);

        \CIBlockElement::SetPropertyValueCode(
            $element['ID'],
            'IMG',
            [
                $element['PROPERTY_IMG_PROPERTY_VALUE_ID'][$position] => [
                    'VALUE' => [
                        'MODULE_ID' => 'iblock',
                        'del'       => 'Y',
                    ],
                ],
            ]
        );
    }
}
