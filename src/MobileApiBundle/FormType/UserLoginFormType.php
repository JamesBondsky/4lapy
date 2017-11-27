<?php

namespace FourPaws\MobileApiBundle\FormType;

use FourPaws\MobileApiBundle\Services\CaptchaFormService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserLoginFormType extends AbstractType
{
    /**
     * @var CaptchaFormService
     */
    private $captchaFormService;

    public function __construct(CaptchaFormService $captchaFormService)
    {
        $this->captchaFormService = $captchaFormService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class)
            ->add('password', TextType::class);
        $this->captchaFormService->config($builder);
    }
}
