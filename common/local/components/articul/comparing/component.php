<?

$this->setFrameMode(true);

$arDefaultUrlTemplates404 = [
    'list'  => '',
    'detail'   => '#SECTION_ID#/',
];

$arComponentVariables = [
    'SECTION_ID',
];

$arDefaultVariableAliases404 = [];

$arVariables = [];

$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
    $arDefaultUrlTemplates404,
    $this->arParams['SEF_URL_TEMPLATES']
);


$engine = new CComponentEngine($this);
$componentPage = $engine->guessComponentPath(
    $this->arParams['SEF_FOLDER'],
    $arUrlTemplates,
    $arVariables
);

// На корневой странице возвращается всегда false
if(!$componentPage){
    $componentPage = 'list';
}

if ($componentPage !== '404') {
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
}

$this->includeComponentTemplate($componentPage);


?>