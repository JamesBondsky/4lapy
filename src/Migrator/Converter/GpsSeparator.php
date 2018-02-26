<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class StoreGpsSeparator
 *
 * !!! специфичный для проекта конвертер
 *
 * Разделяет свойство типа "привязка к карте" на поля для координат склада
 *
 * @package FourPaws\Migrator\Converter
 */
final class GpsSeparator extends AbstractConverter
{
    const GPS_N_FIELD_NAME = 'GPS_N';
    const GPS_S_FIELD_NAME = 'GPS_S';

    private $gpsN = self::GPS_N_FIELD_NAME;
    private $gpsS = self::GPS_S_FIELD_NAME;

    /**
     * @param string $gpsN
     *
     * @return GpsSeparator
     */
    public function setGpsN(string $gpsN): GpsSeparator
    {
        $this->gpsN = $gpsN;

        return $this;
    }

    /**
     * @param string $gpsS
     *
     * @return GpsSeparator
     */
    public function setGpsS(string $gpsS): GpsSeparator
    {
        $this->gpsS = $gpsS;

        return $this;
    }


    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data): array
    {
        $fieldName = $this->getFieldName();

        if (!$data[$fieldName]) {
            return $data;
        }

        $coordinates = explode(',', $data[$fieldName]);

        $data[$this->gpsN] = $coordinates[0];
        $data[$this->gpsS] = $coordinates[1];

        unset($data[$fieldName]);

        return $data;
    }

}
