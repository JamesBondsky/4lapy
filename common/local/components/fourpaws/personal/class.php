<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection AutoloadingIssuesInspection */
class CPersonalCabinetComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        $arDefaultUrlTemplates404 = [
            'personal'  => '',
            'address'   => 'address/',
            'bonus'     => 'bonus/',
            'orders'    => 'orders/',
            'pets'      => 'pets/',
            'referal'   => 'referal/',
            'subscribe' => 'subscribe/',
            'top'       => 'top/',
        ];
        
        $arComponentVariables = [
            'SECTION_ID',
            'SECTION_CODE',
            'ELEMENT_ID',
            'ELEMENT_CODE',
        ];
        
        $arDefaultVariableAliases404 = [];
        
        $arVariables = [];
        
        $arUrlTemplates    = CComponentEngine::makeComponentUrlTemplates(
            $arDefaultUrlTemplates404,
            $this->arParams['SEF_URL_TEMPLATES']
        );
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
            $arDefaultVariableAliases404,
            $this->arParams['VARIABLE_ALIASES']
        );
        
        $engine        = new CComponentEngine($this);
        $componentPage = $engine->guessComponentPath(
            $this->arParams['SEF_FOLDER'],
            $arUrlTemplates,
            $arVariables
        );
        
        if (!$componentPage) {
            $componentPage = 'personal';
        }
        
        CComponentEngine::initComponentVariables(
            $componentPage,
            $arComponentVariables,
            $arVariableAliases,
            $arVariables
        );
        
        /** @noinspection PhpUnusedLocalVariableInspection */
        $arResult = [
            'FOLDER'        => $this->arParams['SEF_FOLDER'],
            'URL_TEMPLATES' => $arUrlTemplates,
            'VARIABLES'     => $arVariables,
            'ALIASES'       => $arVariableAliases,
        ];
        $this->includeComponentTemplate($componentPage);
        
        return true;
    }
}
