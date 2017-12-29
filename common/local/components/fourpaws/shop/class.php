<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\LoaderException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsShopComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     * @throws LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        $arDefaultUrlTemplates404 = [
            'list'   => '',
            'detail' => '#ID#/',
        ];
        
        $arComponentVariables = [
            'ID',
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
            $componentPage = 'list';
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
