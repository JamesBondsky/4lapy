<?php

namespace FourPaws\External\ZagruzkaCom;

class Sms
{

    protected $parameters = [
        'phone' => false,
        'message' => false,
        'fromHour' => false,
        'toHour' => false,
    ];

    public function __construct($phone, $message)
    {
        $this
            ->setPhone($phone)
            ->setMessage($message)
        ;
    }

    public function setPhone($phone) {
        $this->parameters['phone'] = $phone;
        return $this;
    }

    public function setMessage($message) {
        $this->parameters['message'] = $message;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function setFromHour($hour) {
        $this->parameters['fromHour'] = $hour;
        return $this;
    }

    public function setToHour($hour) {
        $this->parameters['toHour'] = $hour;
        return $this;
    }

    public function getPhone() {
        return $this->getParameters()['phone'];
    }

    public function getMessage() {
        return $this->getParameters()['message'];
    }

    public function getFromHour() {
        return $this->getParameters()['fromHour'];
    }

    public function getToHour() {
        return $this->getParameters()['toHour'];
    }

}