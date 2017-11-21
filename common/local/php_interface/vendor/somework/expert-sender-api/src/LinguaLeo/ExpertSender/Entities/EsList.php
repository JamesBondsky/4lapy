<?php

namespace LinguaLeo\ExpertSender\Entities;

class EsList
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $friendlyName;

    /**
     * @var string
     */
    private $language;
    /**
     * @var string
     */
    private $optInMod;

    public function __construct($id, $name, $friendlyName, $language, $optInMod)
    {
        $this
            ->setId($id)
            ->setName($name)
            ->setFriendlyName($friendlyName)
            ->setLanguage($language)
            ->setOptInMod($optInMod);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return EsList
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return EsList
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    /**
     * @param string $friendlyName
     *
     * @return EsList
     */
    public function setFriendlyName($friendlyName)
    {
        $this->friendlyName = (string) $friendlyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return EsList
     */
    public function setLanguage($language)
    {
        $this->language = (string) $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptInMod()
    {
        return $this->optInMod;
    }

    /**
     * @param string $optInMod
     *
     * @return EsList
     */
    public function setOptInMod($optInMod)
    {
        $this->optInMod = (string) $optInMod;

        return $this;
    }
}
