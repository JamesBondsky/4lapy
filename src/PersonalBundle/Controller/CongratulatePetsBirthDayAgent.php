<?php

    namespace FourPaws\PersonalBundle\Controller;


    use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
    use FourPaws\App\Application;

    class CongratulatePetsBirthDayAgent
    {

        use LazyLoggerAwareTrait;

        /** @var CongratulatePetsBirthDayAgent $instance */
        protected static $instance;

        private function __construct() {}
        private function __clone() {}

        /**
         * @return CongratulatePetsBirthDayAgent
         */
        public static function getInstance()
        {
            if(is_null(static::$instance)) {
                static::$instance = new self();
            }

            return static::$instance;
        }

        /**
         *
         */
        public static function sendCongratulations()
        {
            try {
                $birthDayPets = Application::getInstance()->getContainer()->get('pet.service')->getBirthdayPets();
                $sender = Application::getInstance()->getContainer()->get('expertsender.service');
                foreach ($birthDayPets as $pet) {
                    $sender->sendBirthDayCongratulationsEmail($pet);
                }
            }
            catch (\Exception $exception)
            {
                dump($exception);
                $instance = static::getInstance();
                $instance->log()->critical(
                    sprintf(
                        '%s exception: %s',
                        __METHOD__,
                        $exception->getMessage()
                    )
                );
            }

            return '\\' . __METHOD__ . '();';
        }

    }