<?php

    namespace FourPaws\PersonalBundle\Models;


    class PetCongratulationsNotify
    {
        /**
         * @var int
         */
        private $petId;

        /**
         * @var string
         */
        private $petName;

        /**
         * @var string
         */
        private $petType;

        /**
         * @var string
         */
        private $ownerName;

        /**
         * @var string
         */
        private $ownerEmail;

        private $birthDay;

        /**
         * @return mixed
         */
        public function getPetId()
        {
            return $this->petId;
        }

        /**
         * @param mixed $petId
         * @return PetCongratulationsNotify
         */
        public function setPetId($petId)
        {
            $this->petId = $petId;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getPetName()
        {
            return $this->petName;
        }

        /**
         * @param mixed $petName
         * @return PetCongratulationsNotify
         */
        public function setPetName($petName)
        {
            $this->petName = $petName;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getPetType()
        {
            return $this->petType;
        }

        /**
         * @param mixed $petType
         * @return PetCongratulationsNotify
         */
        public function setPetType($petType)
        {
            $this->petType = $petType;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getOwnerName()
        {
            return $this->ownerName;
        }

        /**
         * @param mixed $ownerName
         * @return PetCongratulationsNotify
         */
        public function setOwnerName($ownerName)
        {
            $this->ownerName = $ownerName;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getOwnerEmail()
        {
            return $this->ownerEmail;
        }

        /**
         * @param mixed $ownerEmail
         * @return PetCongratulationsNotify
         */
        public function setOwnerEmail($ownerEmail)
        {
            $this->ownerEmail = $ownerEmail;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getBirthDay()
        {
            return $this->birthDay;
        }

        /**
         * @param mixed $birthDay
         * @return PetCongratulationsNotify
         */
        public function setBirthDay($birthDay)
        {
            $this->birthDay = $birthDay;
            return $this;
        }

        /**
         * @return bool
         */
        public function isDog() :bool
        {
            return $this->petType == 'sobaki';
        }

        /**
         * @return bool
         */
        public function isCat() :bool
        {
            return $this->petType == 'koshki';
        }

        /**
         * @return bool
         */
        public function isOther() :bool
        {
            return !($this->isDog() && $this->isCat());
        }

    }