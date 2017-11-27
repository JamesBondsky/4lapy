<?php

namespace FourPaws\MobileApiBundle\Services;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CaptchaFormService
{
    const ID_FIELD = 'captcha_id';
    const VALUE_FIELD = 'captcha_value';

    /**
     * @var CaptchaServiceInterface
     */
    private $captchaService;

    public function __construct(CaptchaServiceInterface $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    public function config(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add(static::ID_FIELD, TextType::class, ['required' => true, 'empty_data' => ''])
            ->add(static::VALUE_FIELD, TextType::class, ['required' => true, 'empty_data' => ''])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $this->validateCaptcha($event);
            });
    }

    protected function validateCaptcha(FormEvent $formEvent)
    {
        $form = $formEvent->getForm();
        $id = $form->get(static::ID_FIELD)->getData();
        $code = $form->get(static::VALUE_FIELD)->getData();

        if (!$this->captchaService->checkCode($id, $code)) {
            $form->addError(new FormError('Не верный код'));
        }
    }
}
