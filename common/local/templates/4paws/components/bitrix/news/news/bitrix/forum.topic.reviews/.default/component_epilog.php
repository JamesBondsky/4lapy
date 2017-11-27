<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedClassInspection */
/**
 * @var array                      $arParams
 * @var array                      $arResult
 * @var string                     $strErrorMessage
 *
 * @param CBitrixComponent         $component
 * @param CBitrixComponentTemplate $this
 *
 * @global CMain                   $APPLICATION
 */
/** @noinspection PhpUndefinedClassInspection */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if (($_REQUEST['save_product_review'] === 'Y') && $arParams['AJAX_POST'] === 'Y') {
    $response = ob_get_clean();
    $JSResult = [];
    /** @noinspection PhpUndefinedClassInspection */
    $FHParser = new CForumSimpleHTMLParser($response);
    
    $statusMessage             = $FHParser->getTagHTML('div[class=reviews-note-box]');
    $JSResult['statusMessage'] = $statusMessage;
    
    if (empty($_REQUEST['preview_comment']) || $_REQUEST['preview_comment'] === 'N') // message added
    {
        $result = (int)$arResult['RESULT'];
        
        if ($result > 0
            && ((isset($_REQUEST['pageNumber']) && (int)$_REQUEST['pageNumber'] !== $arResult['PAGE_NUMBER'])
                            || (isset($_REQUEST['pageCount']) && (int)$_REQUEST['pageCount'] !== $arResult['PAGE_COUNT']))) {
            $messagePost       = $FHParser->getTagHTML('div[class=reviews-block-inner]');
            $messageNavigation = $FHParser->getTagHTML('div[class=reviews-navigation-box]');
            
            $JSResult += [
                'status'      => true,
                'allMessages' => true,
                'message'     => $messagePost,
                'messageID'   => $result,
                'messagesID'  => array_keys($arResult['MESSAGES']),
                'navigation'  => $messageNavigation,
                'pageNumber'  => $arResult['PAGE_NUMBER'],
                'pageCount'   => $arResult['PAGE_COUNT'],
            ];
            
            if ('' === $messagePost
                && !($arResult['USER']['RIGHTS']['MODERATE'] !== 'Y'
                     && $arResult['FORUM']['MODERATION'] === 'Y')) {
                $JSResult += ['reload' => true];
            }
        } else {
            $JSResult['allMessages'] = false;
            if ($result === false) {
                /** @noinspection PhpUndefinedVariableInspection */
                $JSResult += [
                    'status' => false,
                    'error'  => $arError[0]['title'],
                ];
            } else {
                $messagePost = $FHParser->getTagHTML('table[id=message' . $result . ']');
                $JSResult    += [
                    'status'    => true,
                    'messageID' => $result,
                    'message'   => $messagePost,
                ];
                if ('' === $messagePost
                    && !($result > 0 && $arResult['USER']['RIGHTS']['MODERATE'] !== 'Y'
                         && $arResult['FORUM']['MODERATION'] === 'Y')) {
                    $JSResult += ['reload' => true];
                }
                
                if (strpos($JSResult['message'], 'onForumImageLoad') !== false) {
                    /** @noinspection PhpUndefinedClassInspection */
                    $SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
                    $scripts  = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');
                    
                    if ($scripts !== '') {
                        $JSResult['message'] = $scripts . "\n" . $JSResult['message'];
                    }
                }
            }
        }
    } else // preview
    {
        if (empty($arError)) {
            $messagePreview = $FHParser->getTagHTML('div[class=reviews-preview]');
            $JSResult       += [
                'status'         => true,
                'previewMessage' => $messagePreview,
            ];
            if (strpos($JSResult['previewMessage'], 'onForumImageLoad') !== false) {
                /** @noinspection PhpUndefinedClassInspection */
                $SHParser = new CForumSimpleHTMLParser($APPLICATION->GetHeadStrings());
                $scripts  = $SHParser->getInnerHTML('<!--LOAD_SCRIPT-->', '<!--END_LOAD_SCRIPT-->');
                
                if ($scripts !== '') {
                    $JSResult['previewMessage'] = $scripts . "\n" . $JSResult['previewMessage'];
                }
            }
        } else {
            $JSResult += [
                'status' => false,
                'error'  => $arError[0]['title'],
            ];
        }
    }
    
    $APPLICATION->RestartBuffer();
    /** @noinspection MissingOrEmptyGroupStatementInspection */
    /** @noinspection PhpStatementHasEmptyBodyInspection */
    /** @noinspection LoopWhichDoesNotLoopInspection */
    while (ob_end_clean()) {
    }
    
    if ($request->getPost('dataType') === 'json') {
        header('Content-Type:application/json; charset=UTF-8');
        /** @noinspection PhpUndefinedClassInspection */
        echo \Bitrix\Main\Web\Json::encode($JSResult);
        
    } else {
        /** @noinspection Annotator */
        /** @noinspection PhpUndefinedClassInspection */
        echo '<script>top.SetReviewsAjaxPostTmp(' . CUtil::PhpToJSObject($JSResult) . ');</script>';
    }
    
    /** @noinspection PhpUndefinedClassInspection */
    \CMain::FinalActions();
    die();
}