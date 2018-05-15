<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.18
 * Time: 9:20
 */

namespace FourPaws\Adapter\Model\Input;

use JMS\Serializer\Annotation as Serializer;

class DadataLocation
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $areaFiasId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $areaKladrId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $areaTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $areaType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $areaWithType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("area")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $area = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("beltway_distance")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $beltwayDistance = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("beltway_hit")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $beltwayHit = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("block_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $blockTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("block_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $blockType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("block")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $block = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("capital_marker")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $capitalMarker = ''; // 	0
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_area")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityArea = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ity_district_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrictFiasId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_district_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrictKladrId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_district_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrictTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_district_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrictType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_district_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrictWithType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_district")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityDistrict = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ity_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityFiasId = ''; // 	c2deb16a-0330-4f05-821f-1d09c93331e6
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityKladrId = ''; // 	7800000000000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityTypeFull = ''; // 	город
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityType = ''; // 	г
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityWithType = ''; // 	г Санкт-Петербург
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $city = ''; // 	Санкт-Петербург
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("country")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $country = ''; // 	Россия
    /**
     * @var integer
     * @Serializer\Type("string")
     * @Serializer\SerializedName("fias_actuality_state")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $fiasActualityState = ''; // 	0
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("fias_code")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $fiasCode = ''; // 	78000000000000000000000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $fiasId = ''; // 	c2deb16a-0330-4f05-821f-1d09c93331e6
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("fias_level")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $fiasLevel = ''; // 	1
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("flat_area")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flatArea = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("flat_price")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flatPrice = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("flat_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flatTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("flat_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flatType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("flat")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flat = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("geo_lat")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $geoLat = ''; // 	59.9391313
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("geo_lon")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $geoLon = ''; // 	30.3159004
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("history_values")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $historyValues = ''; // [ = ''; // 	г Ленинград
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $houseFiasId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $houseKladrId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $houseTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $houseType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("house")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $house = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $kladrId = ''; // 	7800000000000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("metro")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $metro = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("okato")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $okato = ''; // 	40000000000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("oktmo")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $oktmo = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("postal_box")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $postalBox = '';
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("postal_code")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $postalCode = ''; // 	190000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("qc_complete")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $qcComplete = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("qc_geo")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $qcGeo = ''; // 	4
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("qc_house")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $qcHouse = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("qc")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $qc = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $regionFiasId = ''; // 	c2deb16a-0330-4f05-821f-1d09c93331e6
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $regionKladrId = ''; // 	7800000000000
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $regionTypeFull = ''; // 	город
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $regionType = ''; // 	г
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $regionWithType = ''; // 	г Санкт-Петербург
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("region")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $region = ''; // 	Санкт-Петербург
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlementFiasId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlementKladrId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlementTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlementType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlementWithType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("settlement")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $settlement = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("source")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $source = ''; // 	г Санкт-Петербург
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("square_meter_price")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $squareMeterPrice = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street_fias_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $streetFiasId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street_kladr_id")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $streetKladrId = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street_type_full")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $streetTypeFull = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $streetType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street_with_type")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $streetWithType = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("street")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $street = '';
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("tax_office_legal")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $taxOfficeLegal = 0; // 	7800
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("tax_office")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $taxOffice = 0; // 	7800
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("timezone")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $timezone = '';
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("unparsed_parts")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $unparsedParts = '';

    /**
     * @return string
     */
    public function getAreaFiasId(): string
    {
        return $this->areaFiasId ?? '';
    }

    /**
     * @param string $areaFiasId
     */
    public function setAreaFiasId(string $areaFiasId): void
    {
        $this->areaFiasId = $areaFiasId;
    }

    /**
     * @return string
     */
    public function getAreaKladrId(): string
    {
        return $this->areaKladrId ?? '';
    }

    /**
     * @param string $areaKladrId
     */
    public function setAreaKladrId(string $areaKladrId): void
    {
        $this->areaKladrId = $areaKladrId;
    }

    /**
     * @return string
     */
    public function getAreaTypeFull(): string
    {
        return $this->areaTypeFull ?? '';
    }

    /**
     * @param string $areaTypeFull
     */
    public function setAreaTypeFull(string $areaTypeFull): void
    {
        $this->areaTypeFull = $areaTypeFull;
    }

    /**
     * @return string
     */
    public function getAreaType(): string
    {
        return $this->areaType ?? '';
    }

    /**
     * @param string $areaType
     */
    public function setAreaType(string $areaType): void
    {
        $this->areaType = $areaType;
    }

    /**
     * @return string
     */
    public function getAreaWithType(): string
    {
        return $this->areaWithType ?? '';
    }

    /**
     * @param string $areaWithType
     */
    public function setAreaWithType(string $areaWithType): void
    {
        $this->areaWithType = $areaWithType;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area ?? '';
    }

    /**
     * @param string $area
     */
    public function setArea(string $area): void
    {
        $this->area = $area;
    }

    /**
     * @return string
     */
    public function getBeltwayDistance(): string
    {
        return $this->beltwayDistance ?? '';
    }

    /**
     * @param string $beltwayDistance
     */
    public function setBeltwayDistance(string $beltwayDistance): void
    {
        $this->beltwayDistance = $beltwayDistance;
    }

    /**
     * @return string
     */
    public function getBeltwayHit(): string
    {
        return $this->beltwayHit ?? '';
    }

    /**
     * @param string $beltwayHit
     */
    public function setBeltwayHit(string $beltwayHit): void
    {
        $this->beltwayHit = $beltwayHit;
    }

    /**
     * @return string
     */
    public function getBlockTypeFull(): string
    {
        return $this->blockTypeFull ?? '';
    }

    /**
     * @param string $blockTypeFull
     */
    public function setBlockTypeFull(string $blockTypeFull): void
    {
        $this->blockTypeFull = $blockTypeFull;
    }

    /**
     * @return string
     */
    public function getBlockType(): string
    {
        return $this->blockType ?? '';
    }

    /**
     * @param string $blockType
     */
    public function setBlockType(string $blockType): void
    {
        $this->blockType = $blockType;
    }

    /**
     * @return string
     */
    public function getBlock(): string
    {
        return $this->block ?? '';
    }

    /**
     * @param string $block
     */
    public function setBlock(string $block): void
    {
        $this->block = $block;
    }

    /**
     * @return string
     */
    public function getCapitalMarker(): string
    {
        return $this->capitalMarker ?? '';
    }

    /**
     * @param string $capitalMarker
     */
    public function setCapitalMarker(string $capitalMarker): void
    {
        $this->capitalMarker = $capitalMarker;
    }

    /**
     * @return string
     */
    public function getCityArea(): string
    {
        return $this->cityArea ?? '';
    }

    /**
     * @param string $cityArea
     */
    public function setCityArea(string $cityArea): void
    {
        $this->cityArea = $cityArea;
    }

    /**
     * @return string
     */
    public function getCityDistrictFiasId(): string
    {
        return $this->cityDistrictFiasId ?? '';
    }

    /**
     * @param string $cityDistrictFiasId
     */
    public function setCityDistrictFiasId(string $cityDistrictFiasId): void
    {
        $this->cityDistrictFiasId = $cityDistrictFiasId;
    }

    /**
     * @return string
     */
    public function getCityDistrictKladrId(): string
    {
        return $this->cityDistrictKladrId ?? '';
    }

    /**
     * @param string $cityDistrictKladrId
     */
    public function setCityDistrictKladrId(string $cityDistrictKladrId): void
    {
        $this->cityDistrictKladrId = $cityDistrictKladrId;
    }

    /**
     * @return string
     */
    public function getCityDistrictTypeFull(): string
    {
        return $this->cityDistrictTypeFull ?? '';
    }

    /**
     * @param string $cityDistrictTypeFull
     */
    public function setCityDistrictTypeFull(string $cityDistrictTypeFull): void
    {
        $this->cityDistrictTypeFull = $cityDistrictTypeFull;
    }

    /**
     * @return string
     */
    public function getCityDistrictType(): string
    {
        return $this->cityDistrictType ?? '';
    }

    /**
     * @param string $cityDistrictType
     */
    public function setCityDistrictType(string $cityDistrictType): void
    {
        $this->cityDistrictType = $cityDistrictType;
    }

    /**
     * @return string
     */
    public function getCityDistrictWithType(): string
    {
        return $this->cityDistrictWithType ?? '';
    }

    /**
     * @param string $cityDistrictWithType
     */
    public function setCityDistrictWithType(string $cityDistrictWithType): void
    {
        $this->cityDistrictWithType = $cityDistrictWithType;
    }

    /**
     * @return string
     */
    public function getCityDistrict(): string
    {
        return $this->cityDistrict ?? '';
    }

    /**
     * @param string $cityDistrict
     */
    public function setCityDistrict(string $cityDistrict): void
    {
        $this->cityDistrict = $cityDistrict;
    }

    /**
     * @return string
     */
    public function getCityFiasId(): string
    {
        return $this->cityFiasId ?? '';
    }

    /**
     * @param string $cityFiasId
     */
    public function setCityFiasId(string $cityFiasId): void
    {
        $this->cityFiasId = $cityFiasId;
    }

    /**
     * @return string
     */
    public function getCityKladrId(): string
    {
        return $this->cityKladrId ?? '';
    }

    /**
     * @param string $cityKladrId
     */
    public function setCityKladrId(string $cityKladrId): void
    {
        $this->cityKladrId = $cityKladrId;
    }

    /**
     * @return string
     */
    public function getCityTypeFull(): string
    {
        return $this->cityTypeFull ?? '';
    }

    /**
     * @param string $cityTypeFull
     */
    public function setCityTypeFull(string $cityTypeFull): void
    {
        $this->cityTypeFull = $cityTypeFull;
    }

    /**
     * @return string
     */
    public function getCityType(): string
    {
        return $this->cityType ?? '';
    }

    /**
     * @param string $cityType
     */
    public function setCityType(string $cityType): void
    {
        $this->cityType = $cityType;
    }

    /**
     * @return string
     */
    public function getCityWithType(): string
    {
        return $this->cityWithType ?? '';
    }

    /**
     * @param string $cityWithType
     */
    public function setCityWithType(string $cityWithType): void
    {
        $this->cityWithType = $cityWithType;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city ?? '';
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country ?? '';
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return int
     */
    public function getFiasActualityState(): int
    {
        return $this->fiasActualityState ?? 0;
    }

    /**
     * @param int $fiasActualityState
     */
    public function setFiasActualityState(int $fiasActualityState): void
    {
        $this->fiasActualityState = $fiasActualityState;
    }

    /**
     * @return string
     */
    public function getFiasCode(): string
    {
        return $this->fiasCode ?? '';
    }

    /**
     * @param string $fiasCode
     */
    public function setFiasCode(string $fiasCode): void
    {
        $this->fiasCode = $fiasCode;
    }

    /**
     * @return string
     */
    public function getFiasId(): string
    {
        return $this->fiasId ?? '';
    }

    /**
     * @param string $fiasId
     */
    public function setFiasId(string $fiasId): void
    {
        $this->fiasId = $fiasId;
    }

    /**
     * @return string
     */
    public function getFiasLevel(): string
    {
        return $this->fiasLevel ?? '';
    }

    /**
     * @param string $fiasLevel
     */
    public function setFiasLevel(string $fiasLevel): void
    {
        $this->fiasLevel = $fiasLevel;
    }

    /**
     * @return string
     */
    public function getFlatArea(): string
    {
        return $this->flatArea ?? '';
    }

    /**
     * @param string $flatArea
     */
    public function setFlatArea(string $flatArea): void
    {
        $this->flatArea = $flatArea;
    }

    /**
     * @return string
     */
    public function getFlatPrice(): string
    {
        return $this->flatPrice ?? '';
    }

    /**
     * @param string $flatPrice
     */
    public function setFlatPrice(string $flatPrice): void
    {
        $this->flatPrice = $flatPrice;
    }

    /**
     * @return string
     */
    public function getFlatTypeFull(): string
    {
        return $this->flatTypeFull ?? '';
    }

    /**
     * @param string $flatTypeFull
     */
    public function setFlatTypeFull(string $flatTypeFull): void
    {
        $this->flatTypeFull = $flatTypeFull;
    }

    /**
     * @return string
     */
    public function getFlatType(): string
    {
        return $this->flatType ?? '';
    }

    /**
     * @param string $flatType
     */
    public function setFlatType(string $flatType): void
    {
        $this->flatType = $flatType;
    }

    /**
     * @return string
     */
    public function getFlat(): string
    {
        return $this->flat ?? '';
    }

    /**
     * @param string $flat
     */
    public function setFlat(string $flat): void
    {
        $this->flat = $flat;
    }

    /**
     * @return string
     */
    public function getGeoLat(): string
    {
        return $this->geoLat ?? '';
    }

    /**
     * @param string $geoLat
     */
    public function setGeoLat(string $geoLat): void
    {
        $this->geoLat = $geoLat;
    }

    /**
     * @return string
     */
    public function getGeoLon(): string
    {
        return $this->geoLon ?? '';
    }

    /**
     * @param string $geoLon
     */
    public function setGeoLon(string $geoLon): void
    {
        $this->geoLon = $geoLon;
    }

    /**
     * @return string
     */
    public function getHistoryValues(): string
    {
        return $this->historyValues ?? '';
    }

    /**
     * @param string $historyValues
     */
    public function setHistoryValues(string $historyValues): void
    {
        $this->historyValues = $historyValues;
    }

    /**
     * @return string
     */
    public function getHouseFiasId(): string
    {
        return $this->houseFiasId ?? '';
    }

    /**
     * @param string $houseFiasId
     */
    public function setHouseFiasId(string $houseFiasId): void
    {
        $this->houseFiasId = $houseFiasId;
    }

    /**
     * @return string
     */
    public function getHouseKladrId(): string
    {
        return $this->houseKladrId ?? '';
    }

    /**
     * @param string $houseKladrId
     */
    public function setHouseKladrId(string $houseKladrId): void
    {
        $this->houseKladrId = $houseKladrId;
    }

    /**
     * @return string
     */
    public function getHouseTypeFull(): string
    {
        return $this->houseTypeFull ?? '';
    }

    /**
     * @param string $houseTypeFull
     */
    public function setHouseTypeFull(string $houseTypeFull): void
    {
        $this->houseTypeFull = $houseTypeFull;
    }

    /**
     * @return string
     */
    public function getHouseType(): string
    {
        return $this->houseType ?? '';
    }

    /**
     * @param string $houseType
     */
    public function setHouseType(string $houseType): void
    {
        $this->houseType = $houseType;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house ?? '';
    }

    /**
     * @param string $house
     */
    public function setHouse(string $house): void
    {
        $this->house = $house;
    }

    /**
     * @return string
     */
    public function getKladrId(): string
    {
        return $this->kladrId ?? '';
    }

    /**
     * @param string $kladrId
     */
    public function setKladrId(string $kladrId): void
    {
        $this->kladrId = $kladrId;
    }

    /**
     * @return string
     */
    public function getMetro(): string
    {
        return $this->metro ?? '';
    }

    /**
     * @param string $metro
     */
    public function setMetro(string $metro): void
    {
        $this->metro = $metro;
    }

    /**
     * @return string
     */
    public function getOkato(): string
    {
        return $this->okato ?? '';
    }

    /**
     * @param string $okato
     */
    public function setOkato(string $okato): void
    {
        $this->okato = $okato;
    }

    /**
     * @return string
     */
    public function getOktmo(): string
    {
        return $this->oktmo ?? '';
    }

    /**
     * @param string $oktmo
     */
    public function setOktmo(string $oktmo): void
    {
        $this->oktmo = $oktmo;
    }

    /**
     * @return string
     */
    public function getPostalBox(): string
    {
        return $this->postalBox ?? '';
    }

    /**
     * @param string $postalBox
     */
    public function setPostalBox(string $postalBox): void
    {
        $this->postalBox = $postalBox;
    }

    /**
     * @return int
     */
    public function getPostalCode(): int
    {
        return $this->postalCode ?? '';
    }

    /**
     * @param int $postalCode
     */
    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getQcComplete(): string
    {
        return $this->qcComplete ?? '';
    }

    /**
     * @param string $qcComplete
     */
    public function setQcComplete(string $qcComplete): void
    {
        $this->qcComplete = $qcComplete;
    }

    /**
     * @return string
     */
    public function getQcGeo(): string
    {
        return $this->qcGeo ?? '';
    }

    /**
     * @param string $qcGeo
     */
    public function setQcGeo(string $qcGeo): void
    {
        $this->qcGeo = $qcGeo;
    }

    /**
     * @return string
     */
    public function getQcHouse(): string
    {
        return $this->qcHouse ?? '';
    }

    /**
     * @param string $qcHouse
     */
    public function setQcHouse(string $qcHouse): void
    {
        $this->qcHouse = $qcHouse;
    }

    /**
     * @return string
     */
    public function getQc(): string
    {
        return $this->qc ?? '';
    }

    /**
     * @param string $qc
     */
    public function setQc(string $qc): void
    {
        $this->qc = $qc;
    }

    /**
     * @return string
     */
    public function getRegionFiasId(): string
    {
        return $this->regionFiasId ?? '';
    }

    /**
     * @param string $regionFiasId
     */
    public function setRegionFiasId(string $regionFiasId): void
    {
        $this->regionFiasId = $regionFiasId;
    }

    /**
     * @return string
     */
    public function getRegionKladrId(): string
    {
        return $this->regionKladrId ?? '';
    }

    /**
     * @param string $regionKladrId
     */
    public function setRegionKladrId(string $regionKladrId): void
    {
        $this->regionKladrId = $regionKladrId;
    }

    /**
     * @return string
     */
    public function getRegionTypeFull(): string
    {
        return $this->regionTypeFull ?? '';
    }

    /**
     * @param string $regionTypeFull
     */
    public function setRegionTypeFull(string $regionTypeFull): void
    {
        $this->regionTypeFull = $regionTypeFull;
    }

    /**
     * @return string
     */
    public function getRegionType(): string
    {
        return $this->regionType ?? '';
    }

    /**
     * @param string $regionType
     */
    public function setRegionType(string $regionType): void
    {
        $this->regionType = $regionType;
    }

    /**
     * @return string
     */
    public function getRegionWithType(): string
    {
        return $this->regionWithType ?? '';
    }

    /**
     * @param string $regionWithType
     */
    public function setRegionWithType(string $regionWithType): void
    {
        $this->regionWithType = $regionWithType;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region ?? '';
    }

    /**
     * @param string $region
     */
    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getSettlementFiasId(): string
    {
        return $this->settlementFiasId ?? '';
    }

    /**
     * @param string $settlementFiasId
     */
    public function setSettlementFiasId(string $settlementFiasId): void
    {
        $this->settlementFiasId = $settlementFiasId;
    }

    /**
     * @return string
     */
    public function getSettlementKladrId(): string
    {
        return $this->settlementKladrId ?? '';
    }

    /**
     * @param string $settlementKladrId
     */
    public function setSettlementKladrId(string $settlementKladrId): void
    {
        $this->settlementKladrId = $settlementKladrId;
    }

    /**
     * @return string
     */
    public function getSettlementTypeFull(): string
    {
        return $this->settlementTypeFull ?? '';
    }

    /**
     * @param string $settlementTypeFull
     */
    public function setSettlementTypeFull(string $settlementTypeFull): void
    {
        $this->settlementTypeFull = $settlementTypeFull;
    }

    /**
     * @return string
     */
    public function getSettlementType(): string
    {
        return $this->settlementType ?? '';
    }

    /**
     * @param string $settlementType
     */
    public function setSettlementType(string $settlementType): void
    {
        $this->settlementType = $settlementType;
    }

    /**
     * @return string
     */
    public function getSettlementWithType(): string
    {
        return $this->settlementWithType ?? '';
    }

    /**
     * @param string $settlementWithType
     */
    public function setSettlementWithType(string $settlementWithType): void
    {
        $this->settlementWithType = $settlementWithType;
    }

    /**
     * @return string
     */
    public function getSettlement(): string
    {
        return $this->settlement ?? '';
    }

    /**
     * @param string $settlement
     */
    public function setSettlement(string $settlement): void
    {
        $this->settlement = $settlement;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source ?? '';
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSquareMeterPrice(): string
    {
        return $this->squareMeterPrice ?? '';
    }

    /**
     * @param string $squareMeterPrice
     */
    public function setSquareMeterPrice(string $squareMeterPrice): void
    {
        $this->squareMeterPrice = $squareMeterPrice;
    }

    /**
     * @return string
     */
    public function getStreetFiasId(): string
    {
        return $this->streetFiasId ?? '';
    }

    /**
     * @param string $streetFiasId
     */
    public function setStreetFiasId(string $streetFiasId): void
    {
        $this->streetFiasId = $streetFiasId;
    }

    /**
     * @return string
     */
    public function getStreetKladrId(): string
    {
        return $this->streetKladrId ?? '';
    }

    /**
     * @param string $streetKladrId
     */
    public function setStreetKladrId(string $streetKladrId): void
    {
        $this->streetKladrId = $streetKladrId;
    }

    /**
     * @return string
     */
    public function getStreetTypeFull(): string
    {
        return $this->streetTypeFull ?? '';
    }

    /**
     * @param string $streetTypeFull
     */
    public function setStreetTypeFull(string $streetTypeFull): void
    {
        $this->streetTypeFull = $streetTypeFull;
    }

    /**
     * @return string
     */
    public function getStreetType(): string
    {
        return $this->streetType ?? '';
    }

    /**
     * @param string $streetType
     */
    public function setStreetType(string $streetType): void
    {
        $this->streetType = $streetType;
    }

    /**
     * @return string
     */
    public function getStreetWithType(): string
    {
        return $this->streetWithType ?? '';
    }

    /**
     * @param string $streetWithType
     */
    public function setStreetWithType(string $streetWithType): void
    {
        $this->streetWithType = $streetWithType;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street ?? '';
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return int
     */
    public function getTaxOfficeLegal(): int
    {
        return $this->taxOfficeLegal ?? 0;
    }

    /**
     * @param int $taxOfficeLegal
     */
    public function setTaxOfficeLegal(int $taxOfficeLegal): void
    {
        $this->taxOfficeLegal = $taxOfficeLegal;
    }

    /**
     * @return int
     */
    public function getTaxOffice(): int
    {
        return $this->taxOffice ?? 0;
    }

    /**
     * @param int $taxOffice
     */
    public function setTaxOffice(int $taxOffice): void
    {
        $this->taxOffice = $taxOffice;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? '';
    }

    /**
     * @param string $timezone
     */
    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getUnparsedParts(): string
    {
        return $this->unparsedParts ?? '';
    }

    /**
     * @param string $unparsedParts
     */
    public function setUnparsedParts(string $unparsedParts): void
    {
        $this->unparsedParts = $unparsedParts;
    }
}