<?php use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Model\IblockSect;
use FourPaws\BitrixOrm\Query\IblockSectQuery;
use FourPaws\Enum\Form;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\FormHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $faqCategoryId;

/**
 * @global     $APPLICATION
 * @global int $faqCategoryId
 */
try {
    $faqIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::FAQ);
} catch (IblockNotFoundException $e) {
    $faqIblockId = 0;
}
if ($faqIblockId <= 0) {
    return;
}
$faqCount = 10;

$faqSectId = 0;
$userFaqSectId = 0;

/** @var IblockSect $mainSect */
$mainSect = (new IblockSectQuery())->withFilter([
    'IBLOCK_ID'   => $faqIblockId,
    'ACTIVE'      => 'Y',
    'DEPTH_LEVEL' => 1,
    '=ID'         => $faqCategoryId,
])->withOrder(['SORT' => 'ASC'])->withNav(['nTopCount' => 1])->exec()->first();
$childrenSections = (new IblockSectQuery())->withFilter([
    'IBLOCK_ID'   => $faqIblockId,
    'DEPTH_LEVEL' => 2,
    'SECTION_ID'  => $mainSect->getId(),
    'ACTIVE'      => 'Y',
])->withOrder(['SORT' => 'ASC'])->withNav(['nTopCount' => 2])->exec();

$i = 0;
foreach ($childrenSections as $key => $childrenSection) {
    $i++;
    if ($i === 1) {
        $faqSectId = $childrenSection->getId();
    } else {
        $userFaqSectId = $childrenSection->getId();
    }
} ?>
<div class="b-container fleas-protection-container">
    <div class="fleas-protection-block">
        <div class="fleas-protection-block__questions">
            <div class="fleas-protection-block__questions--title"><?= $mainSect->getName() ?></div>
            <div class="fleas-protection-block__questions--tabs">
                <div class="fleas-protection-block__questions--tabs-wrapper">
                    <?php /** @var IblockSect $childrenSection */
                    $i = 0;
                    foreach ($childrenSections as $key => $childrenSection) {
                        $i++; ?>
                        <div class="fleas-protection-block__questions--tab <?= $i === 1 ? ' active' : '' ?> js-question-tab"
                             data-type="<?= $childrenSection->getCode() ?>">
                            <?= $childrenSection->getName() ?>
                        </div>
                    <?php } ?>
                    <div class="fleas-protection-block__questions--tab js-form-move">Задать вопрос</div>
                </div>
            </div>
            <div class="fleas-protection-block__questions--blocks">
                <?php if ($faqSectId > 0) {
                    $APPLICATION->IncludeComponent('bitrix:news.list',
                        'faq',
                        [
                            'COMPONENT_TEMPLATE'              => 'faq',
                            'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
                            'IBLOCK_ID'                       => $faqIblockId,
                            'NEWS_COUNT'                      => $faqCount,
                            'SORT_BY1'                        => 'SORT',
                            'SORT_ORDER1'                     => 'ASC',
                            'SORT_BY2'                        => 'ACTIVE_FROM',
                            'SORT_ORDER2'                     => 'DESC',
                            'FILTER_NAME'                     => '',
                            'FIELD_CODE'                      => [
                                0 => 'NAME',
                                1 => 'PREVIEW_TEXT',
                                2 => 'DETAIL_TEXT',
                                3 => '',
                            ],
                            'PROPERTY_CODE'                   => [],
                            'CHECK_DATES'                     => 'Y',
                            'DETAIL_URL'                      => '',
                            'AJAX_MODE'                       => 'N',
                            'AJAX_OPTION_JUMP'                => 'N',
                            'AJAX_OPTION_STYLE'               => 'N',
                            'AJAX_OPTION_HISTORY'             => 'N',
                            'AJAX_OPTION_ADDITIONAL'          => '',
                            'CACHE_TYPE'                      => 'A',
                            'CACHE_TIME'                      => '36000000',
                            'CACHE_FILTER'                    => 'Y',
                            'CACHE_GROUPS'                    => 'N',
                            'PREVIEW_TRUNCATE_LEN'            => '',
                            'ACTIVE_DATE_FORMAT'              => '',
                            'SET_TITLE'                       => 'N',
                            'SET_BROWSER_TITLE'               => 'N',
                            'SET_META_KEYWORDS'               => 'N',
                            'SET_META_DESCRIPTION'            => 'N',
                            'SET_LAST_MODIFIED'               => 'N',
                            'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
                            'ADD_SECTIONS_CHAIN'              => 'N',
                            'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
                            'PARENT_SECTION'                  => $faqSectId,
                            'PARENT_SECTION_CODE'             => '',
                            'INCLUDE_SUBSECTIONS'             => 'N',
                            'STRICT_SECTION_CHECK'            => 'N',
                            'DISPLAY_DATE'                    => 'N',
                            'DISPLAY_NAME'                    => 'N',
                            'DISPLAY_PICTURE'                 => 'N',
                            'DISPLAY_PREVIEW_TEXT'            => 'N',
                            'PAGER_TEMPLATE'                  => '',
                            'DISPLAY_TOP_PAGER'               => 'N',
                            'DISPLAY_BOTTOM_PAGER'            => 'N',
                            'PAGER_TITLE'                     => '',
                            'PAGER_SHOW_ALWAYS'               => 'N',
                            'PAGER_DESC_NUMBERING'            => 'N',
                            'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
                            'PAGER_SHOW_ALL'                  => 'N',
                            'PAGER_BASE_LINK_ENABLE'          => 'N',
                            'SET_STATUS_404'                  => 'N',
                            'SHOW_404'                        => 'N',
                            'MESSAGE_404'                     => '',
                        ],
                        false,
                        ['HIDE_ICONS' => 'Y']);
                }
                if ($userFaqSectId > 0) { ?>
                    <div class="fleas-protection-block__questions--block js-question-block" data-type="users-questions">
                        <?php /** включен стоковый аякс из-за пагинации, не првоеряем по датам так как использвем дату начало для отображения */
                        $APPLICATION->IncludeComponent('bitrix:news.list',
                            'user_faq',
                            [
                                'COMPONENT_TEMPLATE'              => 'user_faq',
                                'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
                                'IBLOCK_ID'                       => $faqIblockId,
                                'NEWS_COUNT'                      => $faqCount,
                                'SORT_BY1'                        => 'ACTIVE_FROM',
                                'SORT_ORDER1'                     => 'DESC',
                                'SORT_BY2'                        => 'SORT',
                                'SORT_ORDER2'                     => 'ASC',
                                'FILTER_NAME'                     => '',
                                'FIELD_CODE'                      => [
                                    0 => 'NAME',
                                    1 => 'PREVIEW_TEXT',
                                    2 => 'DETAIL_TEXT',
                                    3 => '',
                                ],
                                'PROPERTY_CODE'                   => [],
                                'CHECK_DATES'                     => 'N',
                                'DETAIL_URL'                      => '',
                                'AJAX_MODE'                       => 'Y',
                                'AJAX_OPTION_JUMP'                => 'N',
                                'AJAX_OPTION_STYLE'               => 'N',
                                'AJAX_OPTION_HISTORY'             => 'N',
                                'AJAX_OPTION_ADDITIONAL'          => '',
                                'CACHE_TYPE'                      => 'A',
                                'CACHE_TIME'                      => '36000000',
                                'CACHE_FILTER'                    => 'Y',
                                'CACHE_GROUPS'                    => 'N',
                                'PREVIEW_TRUNCATE_LEN'            => '',
                                'ACTIVE_DATE_FORMAT'              => 'j F Y',
                                'SET_TITLE'                       => 'N',
                                'SET_BROWSER_TITLE'               => 'N',
                                'SET_META_KEYWORDS'               => 'N',
                                'SET_META_DESCRIPTION'            => 'N',
                                'SET_LAST_MODIFIED'               => 'N',
                                'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
                                'ADD_SECTIONS_CHAIN'              => 'N',
                                'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
                                'PARENT_SECTION'                  => $userFaqSectId,
                                'PARENT_SECTION_CODE'             => '',
                                'INCLUDE_SUBSECTIONS'             => 'N',
                                'STRICT_SECTION_CHECK'            => 'N',
                                'DISPLAY_DATE'                    => 'N',
                                'DISPLAY_NAME'                    => 'N',
                                'DISPLAY_PICTURE'                 => 'N',
                                'DISPLAY_PREVIEW_TEXT'            => 'N',
                                'PAGER_TEMPLATE'                  => 'pagination',
                                'DISPLAY_TOP_PAGER'               => 'N',
                                'DISPLAY_BOTTOM_PAGER'            => 'Y',
                                'PAGER_TITLE'                     => '',
                                'PAGER_SHOW_ALWAYS'               => 'N',
                                'PAGER_DESC_NUMBERING'            => 'N',
                                'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
                                'PAGER_SHOW_ALL'                  => 'N',
                                'PAGER_BASE_LINK_ENABLE'          => 'N',
                                'SET_STATUS_404'                  => 'N',
                                'SHOW_404'                        => 'N',
                                'MESSAGE_404'                     => '',
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php $APPLICATION->IncludeComponent(
            'bitrix:form.result.new',
            'faq',
            [
                'CACHE_TIME'             => '3600000',
                'CACHE_TYPE'             => 'A',
                'CHAIN_ITEM_LINK'        => '',
                'CHAIN_ITEM_TEXT'        => '',
                'EDIT_URL'               => '',
                'IGNORE_CUSTOM_TEMPLATE' => 'Y',
                'LIST_URL'               => '',
                'SEF_MODE'               => 'N',
                'SUCCESS_URL'            => '',
                'USE_EXTENDED_ERRORS'    => 'Y',
                'VARIABLE_ALIASES'       => [
                    'RESULT_ID'   => 'RESULT_ID',
                    'WEB_FORM_ID' => 'WEB_FORM_ID',
                ],
                'WEB_FORM_ID'            => FormHelper::getIdByCode(Form::FAQ),
            ]
        ); ?>
    </div>
</div>
