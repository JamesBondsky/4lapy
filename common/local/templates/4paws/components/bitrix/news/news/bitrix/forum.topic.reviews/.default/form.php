<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedClassInspection */
/**
 * Bitrix vars
 *
 * @var array                    $arParams , $arResult
 * @var CBitrixComponentTemplate $this
 * @var CMain                    $APPLICATION
 * @var CUser                    $USER
 */
$tabIndex = 1;
?><?php if ($arParams['SHOW_MINIMIZED'] === 'Y') {
    ?>
    <!--suppress ALL -->







    <div class="reviews-collapse reviews-minimized" style='position:relative; float:none;'>
        <a class="reviews-collapse-link"
           id="sw<?= $arParams['FORM_ID'] ?>"
           onclick="BX.onCustomEvent(BX('<?= $arParams['FORM_ID'] ?>'), 'onTransverse')"
           href="javascript:void(0);"><?= $arParams['MINIMIZED_EXPAND_TEXT'] ?></a>
    </div>
    <?php
}
?>
    
    <a name="review_anchor"></a>
<?php
if (!empty($arResult['ERROR_MESSAGE'])):
    $arResult['ERROR_MESSAGE'] =
        preg_replace([
                         '/<br(.*?)><br(.*?)>/is',
                         '/<br(.*?)>$/is',
                     ],
                     [
                         '<br />',
                         '',
                     ],
                     $arResult['ERROR_MESSAGE']);
    ?>
    <div class="reviews-note-box reviews-note-error">
        <div class="reviews-note-box-text"><?ShowError($arResult['ERROR_MESSAGE'], 'reviews-note-error'); ?></div>
    </div>
    <?
endif;
?>
    <div class="reviews-reply-form" <?= (($arParams['SHOW_MINIMIZED'] === 'Y') ? 'style="display:none;"' : '') ?>>
        <form name="<?= $arParams['FORM_ID'] ?>"
              id="<?= $arParams['FORM_ID'] ?>"
              action="<?= POST_FORM_ACTION_URI ?>#postform"<?php
        ?>
              method="POST"
              enctype="multipart/form-data"
              class="reviews-form">
            <script type="text/javascript">
                BX.ready(function () {
                    BX.Forum.Init({
                                      id: <?=/** @noinspection PhpUndefinedClassInspection */CUtil::PhpToJSObject(array_keys($arResult['MESSAGES']))?>,
                                      form:          BX('<?=$arParams['FORM_ID']?>'),
                                      preorder:      '<?=$arParams['PREORDER']?>',
                                      pageNumber: <?=(int)$arResult['PAGE_NUMBER'];?>,
                                      pageCount: <?=(int)$arResult['PAGE_COUNT'];?>,
                                      bVarsFromForm: '<?=$arParams['bVarsFromForm']?>',
                                      ajaxPost:      '<?=$arParams['AJAX_POST']?>',
                                      lheId:         'REVIEW_TEXT'
                                  });
                    <?php if ($arParams['SHOW_MINIMIZED'] === 'Y')
                    {
                    ?>
                    BX.addCustomEvent(BX('<?=$arParams['FORM_ID']?>'), 'onBeforeHide', function () {
                        var link = BX('sw<?=$arParams['FORM_ID']?>');
                        if (link) {
                            link.innerHTML = BX.message('MINIMIZED_EXPAND_TEXT');
                            BX.removeClass(BX.addClass(link.parentNode, "reviews-expanded"), "reviews-minimized");
                        }
                    });
                    BX.addCustomEvent(BX('<?=$arParams['FORM_ID']?>'), 'onBeforeShow', function () {
                        var link = BX('sw<?=$arParams['FORM_ID']?>');
                        if (link) {
                            link.innerHTML = BX.message('MINIMIZED_MINIMIZE_TEXT');
                            BX.removeClass(BX.addClass(link.parentNode, "reviews-minimized"), "reviews-expanded");
                        }
                    });
                    <?php
                    }
                    ?>
                });
            </script>
            <input type="hidden" name="index" value="<?= htmlspecialcharsbx($arParams['form_index']) ?>" />
            <input type="hidden" name="back_page" value="<?= $arResult['CURRENT_PAGE'] ?>" />
            <input type="hidden" name="ELEMENT_ID" value="<?= $arParams['ELEMENT_ID'] ?>" />
            <input type="hidden" name="SECTION_ID" value="<?= $arResult['ELEMENT_REAL']['IBLOCK_SECTION_ID'] ?>" />
            <input type="hidden" name="save_product_review" value="Y" />
            <input type="hidden" name="preview_comment" value="N" />
            <input type="hidden" name="AJAX_POST" value="<?= $arParams['AJAX_POST'] ?>" />
            <?= bitrix_sessid_post() ?>
            <?php
            if ($arParams['AUTOSAVE']) {
                $arParams['AUTOSAVE']->Init();
            }
            ?>
            <div style="position:relative; display: block; width:100%;">
                <?php
                /* GUEST PANEL */
                if (!$arResult['IS_AUTHORIZED']):
                    ?>
                    <div class="reviews-reply-fields">
                        <div class="reviews-reply-field-user">
                            <div class="reviews-reply-field reviews-reply-field-author">
                                <label for="REVIEW_AUTHOR<?= $arParams['form_index'] ?>"><?= GetMessage('OPINIONS_NAME') ?><?php
                                    ?><span class="reviews-required-field">*</span></label>
                                <span><input name="REVIEW_AUTHOR"
                                             id="REVIEW_AUTHOR<?= $arParams['form_index'] ?>"
                                             size="30"
                                             type="text"
                                             value="<?= $arResult['REVIEW_AUTHOR'] ?>"
                                             tabindex="<?= $tabIndex++; ?>" /></span></div>
                            <?php
                            if ($arResult['FORUM']['ASK_GUEST_EMAIL'] === 'Y'):
                                ?>
                                <div class="reviews-reply-field-user-sep">&nbsp;</div>
                                <div class="reviews-reply-field reviews-reply-field-email">
                                    <label for="REVIEW_EMAIL<?= $arParams['form_index'] ?>"><?= GetMessage('OPINIONS_EMAIL') ?></label>
                                    <span><input type="text"
                                                 name="REVIEW_EMAIL"
                                                 id="REVIEW_EMAIL<?= $arParams['form_index'] ?>"
                                                 size="30"
                                                 value="<?= $arResult['REVIEW_EMAIL'] ?>"
                                                 tabindex="<?= $tabIndex++; ?>" /></span></div>
                                <?
                            endif;
                            ?>
                            <div class="reviews-clear-float"></div>
                        </div>
                    </div>
                    <?
                endif;
                ?>
                <div class="reviews-reply-header">
                    <span><?= $arParams['MESSAGE_TITLE'] ?></span><span class="reviews-required-field">*</span></div>
                <div class="reviews-reply-field reviews-reply-field-text">
                    <?php
                    /** @noinspection PhpUndefinedClassInspection */
                    /** @noinspection PhpUndefinedClassInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    $APPLICATION->IncludeComponent('bitrix:main.post.form',
                                                   '',
                                                   [
                                                       'FORM_ID'   => $arParams['FORM_ID'],
                                                       'SHOW_MORE' => 'Y',
                                                       'PARSER'    => forumTextParser::GetEditorToolbar(['forum' => $arResult['FORUM']]),
    
                                                       'LHE' => [
                                                           'id'                  => 'REVIEW_TEXT',
                                                           'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT']
                                                                                    === 'Y',
                                                           'bResizable'          => true,
                                                           'bAutoResize'         => true,
                                                           'documentCSS'         => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
                                                           'setFocusAfterShow'   => false,
                                                       ],
    
                                                       'ADDITIONAL' => [],
    
                                                       'TEXT' => [
                                                           'ID'     => 'REVIEW_TEXT',
                                                           'NAME'   => 'REVIEW_TEXT',
                                                           'VALUE'  => $arResult['REVIEW_TEXT'] ?? '',
                                                           'SHOW'   => 'Y',
                                                           'HEIGHT' => '200px',
                                                       ],
    
                                                       'SMILES'        => COption::GetOptionInt('forum',
                                                                                                'smile_gallery_id',
                                                                                                0),
                                                       'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
                                                   ],
                                                   $component,
                                                   ['HIDE_ICONS' => 'Y']);
                    ?>
                </div>
                <?php
                
                /* CAPTHCA */
                if (strlen($arResult['CAPTCHA_CODE']) > 0):
                    ?>
                    <div class="reviews-reply-field reviews-reply-field-captcha">
                        <input type="hidden" name="captcha_code" value="<?= $arResult['CAPTCHA_CODE'] ?>" />
                        <div class="reviews-reply-field-captcha-label">
                            <label for="captcha_word"><?= GetMessage('F_CAPTCHA_PROMT') ?>
                                <span class="reviews-required-field">*</span></label>
                            <input type="text"
                                   size="30"
                                   name="captcha_word"
                                   tabindex="<?= $tabIndex++; ?>"
                                   autocomplete="off" />
                        </div>
                        <div class="reviews-reply-field-captcha-image">
                            <img src="/bitrix/tools/captcha.php?captcha_code=<?= $arResult['CAPTCHA_CODE'] ?>"
                                 alt="<?= GetMessage('F_CAPTCHA_TITLE') ?>" />
                        </div>
                    </div>
                    <?
                endif;
                /* ATTACH FILES */
                if ($arResult['SHOW_PANEL_ATTACH_IMG'] === 'Y'):
                    ?>
                    <div class="reviews-reply-field reviews-reply-field-upload">
                        <?php
                        $iCount = 0;
                        if (is_array($arResult['REVIEW_FILES']) && !empty($arResult['REVIEW_FILES'])):
                            foreach ($arResult['REVIEW_FILES'] as $key => $val):
                                $iCount++;
                                /** @noinspection PhpUndefinedClassInspection */
                                $sFileSize = CFile::FormatSize((int)$val['FILE_SIZE']);
                                ?>
                                <div class="reviews-uploaded-file">
                                    <input type="hidden" name="FILES[<?= $key ?>]" value="<?= $key ?>" />
                                    <input type="checkbox"
                                           name="FILES_TO_UPLOAD[<?= $key ?>]"
                                           id="FILES_TO_UPLOAD_<?= $key ?>"
                                           value="<?= $key ?>"
                                           checked="checked" />
                                    <label for="FILES_TO_UPLOAD_<?= $key ?>"><?= $val['ORIGINAL_NAME'] ?>
                                        (<?= $val['CONTENT_TYPE'] ?>) <?= $sFileSize ?>
                                        (
                                        <a href="/bitrix/components/bitrix/forum.interface/show_file.php?action=download&amp;fid=<?= $key ?>"><?= GetMessage('F_DOWNLOAD') ?></a>
                                        )
                                    </label>
                                </div>
                                <?
                            endforeach;
                        endif;
                        if ($iCount < $arParams['FILES_COUNT']):
                            /** @noinspection PhpUndefinedClassInspection */
                            /** @noinspection PhpUndefinedClassInspection */
                            $sFileSize =
                                CFile::FormatSize((int)COption::GetOptionString('forum', 'file_max_size', 5242880));
                            ?>
                            <div class="reviews-upload-info"
                                 style="display:none;"
                                 id="upload_files_info_<?= $arParams['form_index'] ?>">
                                <?php
                                if ($arParams['FORUM']['ALLOW_UPLOAD'] === 'F'):
                                    ?>
                                    <span><?= str_replace('#EXTENSION#',
                                                          $arParams['FORUM']['ALLOW_UPLOAD_EXT'],
                                                          GetMessage('F_FILE_EXTENSION')) ?></span>
                                    <?
                                endif;
                                ?>
                                <span><?= str_replace('#SIZE#', $sFileSize, GetMessage('F_FILE_SIZE')) ?></span>
                            </div>
                            <?php
                            
                            for ($ii = $iCount; $ii < $arParams['FILES_COUNT']; $ii++):
                                ?>
                                
                                <div class="reviews-upload-file"
                                     style="display:none;"
                                     id="upload_files_<?= $ii ?>_<?= $arParams['form_index'] ?>">
                                    <input name="FILE_NEW_<?= $ii ?>" type="file" value="" size="30" />
                                </div>
                                <?
                            endfor;
                            ?>
                            <a class="forum-upload-file-attach"
                               href="javascript:void(0);"
                               onclick="AttachFile('<?= $iCount ?>', '<?= ($ii
                                                                           - $iCount) ?>', '<?= $arParams['form_index'] ?>', this); return false;">
                                <span><?= GetMessage(($arResult['FORUM']['ALLOW_UPLOAD'] === 'Y') ? 'F_LOAD_IMAGE' : 'F_LOAD_FILE') ?></span>
                            </a>
                            <?
                        endif;
                        ?>
                    </div>
                    <?
                endif;
                ?>
                <div class="reviews-reply-field reviews-reply-field-settings">
                    <?php
                    /* SMILES */
                    if ($arResult['FORUM']['ALLOW_SMILES'] === 'Y'):
                        ?>
                        <div class="reviews-reply-field-setting">
                            <input type="checkbox"
                                   name="REVIEW_USE_SMILES"
                                   id="REVIEW_USE_SMILES<?= $arParams['form_index'] ?>"
                                   <?php
                                   ?>value="Y" <?= ($arResult['REVIEW_USE_SMILES'] === 'Y') ? 'checked="checked"' : ''; ?>
                                   <?php
                                   ?>tabindex="<?= $tabIndex++; ?>" /><?php
                            ?>
                            &nbsp;<label for="REVIEW_USE_SMILES<?= $arParams['form_index'] ?>"><?= GetMessage('F_WANT_ALLOW_SMILES') ?></label>
                        </div>
                        <?
                    endif;
                    /* SUBSCRIBE */
                    if ($arResult['SHOW_SUBSCRIBE'] === 'Y'):
                        ?>
                        <div class="reviews-reply-field-setting">
                            <input type="checkbox"
                                   name="TOPIC_SUBSCRIBE"
                                   id="TOPIC_SUBSCRIBE<?= $arParams['form_index'] ?>"
                                   value="Y" <?php
                            ?><?= ($arResult['TOPIC_SUBSCRIBE'] === 'Y') ? 'checked disabled ' : ''; ?>
                                   tabindex="<?= $tabIndex++; ?>" /><?php
                            ?>
                            &nbsp;<label for="TOPIC_SUBSCRIBE<?= $arParams['form_index'] ?>"><?= GetMessage('F_WANT_SUBSCRIBE_TOPIC') ?></label>
                        </div>
                        <?
                    endif;
                    ?>
                </div>
                <?php
                
                ?>
                <div class="reviews-reply-buttons">
                    <input name="send_button"
                           type="submit"
                           value="<?= GetMessage('OPINIONS_SEND') ?>"
                           tabindex="<?= $tabIndex++; ?>"
                           <?php
                           ?>onclick="this.form.preview_comment.value = 'N';" />
                    <input name="view_button"
                           type="submit"
                           value="<?= GetMessage('OPINIONS_PREVIEW') ?>"
                           tabindex="<?= $tabIndex++; ?>"
                           <?php
                           ?>onclick="this.form.preview_comment.value = 'VIEW';" />
                </div>
            
            </div>
        </form>
    </div>
<?php
if ($arParams['AUTOSAVE']) {
    /** @noinspection PhpUndefinedClassInspection */
    $arParams['AUTOSAVE']->LoadScript([
                                          'formID'    => CUtil::JSEscape($arParams['FORM_ID']),
                                          'controlID' => 'REVIEW_TEXT',
                                      ]);
}
?>