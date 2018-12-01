<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?if( $arParams["LOGIN_FORM"] ):?>
    <?if( $arResult["CONFIRM_EMAIL"] ):?>
        <input type="hidden" id="bxmodAuthShowConfirm" value="<?=$arResult["CONFIRM_EMAIL"]["SUCCESS"]?>">
    <?elseif( $arResult["RESTORE_PASS"] ):?>
        <input type="hidden" id="bxmodAuthShowRestore" value="<?=$arResult["RESTORE_PASS"]["LOGIN_TYPE"]?>">
    <?endif?>
    <div class="bxmodAuthDialogOver"></div>
    <div class="bxmodAuthDialog<?if( $arParams["OPTIONS"]["USE_EMAIL"] != "Y" && $arParams["OPTIONS"]["USE_PHONE"] != "Y" ):?> taOnlySocial<?elseif( $arParams["OPTIONS"]["USE_SOCIAL"] != "Y" ):?> taOnlyLogin<?endif?>">
        <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_CLOSE_TITLE')?>" class="bxmodAuthDialogClose"></a>
        <input type="hidden" class="bxmodAuthDocLocation" value="<?=$arParams["DOCUMENT_LOCATION"]?>">
    
    <?if( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" || $arParams["OPTIONS"]["USE_PHONE"] == "Y" ):?>
        <div class="bxmodAuthLeft">
            <div class="bxmodAuthContainer">
                <div class="bxmodAuthForms">
                    <form class="bxmodAuthLogin taActive">
                        <div class="bxmodAuthTitle">
                            <?if( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" && $arParams["OPTIONS"]["USE_PHONE"] == "Y" ):?>
                                <?=GetMessage('BXMOD_AUTH_TITLE_EMAIL_OR_PHONE')?>
                            <?elseif( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" ):?>
                                <?=GetMessage('BXMOD_AUTH_TITLE_EMAIL')?>
                            <?else:?>
                                <?=GetMessage('BXMOD_AUTH_TITLE_PHONE')?>
                            <?endif?>
                        </div>
                        <div>
                            <?if( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" && $arParams["OPTIONS"]["USE_PHONE"] == "Y" ):?>
                                <?$label = GetMessage('BXMOD_AUTH_YOUR_EMAIL_OR_PHONE')?>
                                <?$placeholder = GetMessage('BXMOD_AUTH_YOUR_EMAIL_OR_PHONE_PLACEHOLDER')?>
                            <?elseif( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" ):?>
                                <?$label = GetMessage('BXMOD_AUTH_YOUR_EMAIL')?>
                                <?$placeholder = GetMessage('BXMOD_AUTH_YOUR_EMAIL_PLACEHOLDER')?>
                            <?else:?>
                                <?$label = GetMessage('BXMOD_AUTH_YOUR_PHONE')?>
                                <?$placeholder = GetMessage('BXMOD_AUTH_YOUR_PHONE_PLACEHOLDER')?>
                            <?endif?>
                            <label><?=$label?>:</label>
                            <input type="text" name="bxmodAuthEmail" value="<?=$arResult["USER_LOGIN"]?>" placeholder="<?=$placeholder?>">
                        </div>
                        <div>
                            <label><?=GetMessage('BXMOD_AUTH_YOUR_PASSWORD')?>:</label>
                            <input type="password" name="bxmodAuthPass" value="" placeholder="<?=GetMessage('BXMOD_AUTH_YOUR_PASSWORD_PLACEHOLDER')?>">
                        </div>
                        
                        <?if( $arParams["OPTIONS"]["EMAIL_CAPTCHA"] == "Y" ):?>
                        <div class="bxmodAuthCaptchaBlock taCaptchaLogin<?if( $arParams["OPTIONS"]["EMAIL_CAPTCHA"] == "Y" && $arParams["OPTIONS"]["EMAIL_CAPTCHA_COUNT"] > 0 ):?> hidden<?endif?>">
                            <div class="bxmodAuthCaptchaInp">
                                <label><?=GetMessage('BXMOD_AUTH_ENTER_CODE')?>:</label>
                                <input type="hidden" name="bxmodAuthCaptchaSid" value="<?=$arResult["CAPTCHA_LOGIN_CODE"]?>" class="captchaSid">
                                <input type="text" name="bxmodAuthCaptcha" placeholder="<?=GetMessage('BXMOD_AUTH_ENTER_CODE_PLACEHOLDER')?>" class="captchaWord">
                            </div>
                            <div class="bxmodAuthCaptchaImg">
                                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_LOGIN_CODE"]?>" alt="<?=GetMessage('BXMOD_AUTH_YOU_ARE_ROBOT')?>" class="captcha">
                            </div>
                            <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_CAPTCHA_REFRESH_TITLE')?>"><?=GetMessage('BXMOD_AUTH_CAPTCHA_REFRESH')?></a>
                        </div>
                        <?endif?>
                        
                        <?if( $arParams["OPTIONS"]["EMAIL_REMIND"] == "Y" ):?>
                        <div>
                            <label class="bxmodAuthRemember"><input type="checkbox" name="bxmodAuthRemember"> <?=GetMessage('BXMOD_AUTH_REMIND_ME')?></label>
                        </div>
                        <?endif?>
                        <div class="bxmodAuthButtons">
                            <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_TO_RESTORE_PASS')?>" class="bxmodAuthToRestore"><?=GetMessage('BXMOD_AUTH_FORGOT_PASSWORD')?></a>
                            <button type="button" title="<?=GetMessage('BXMOD_AUTH_LOGIN_AND_REGISTER')?>" value="<?=GetMessage('BXMOD_AUTH_ONE_MOMENT')?>" class="bxmodAuthLoginButton"><?=GetMessage('BXMOD_AUTH_LOGIN_AND_REGISTER')?></button>
                        </div>
                    </form>
                    <form class="bxmodAuthRestore">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_RESTORE_PASSWORD_TITLE')?></div>
                        <?if( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" && $arParams["OPTIONS"]["USE_PHONE"] == "Y" ):?>
                            <?$label = GetMessage('BXMOD_AUTH_RESTORE_EMAIL_OR_PHONE')?>
                            <?$placeholder = GetMessage('BXMOD_AUTH_RESTORE_EMAIL_OR_PHONE_PLACEHOLDER')?>
                        <?elseif( $arParams["OPTIONS"]["USE_EMAIL"] == "Y" ):?>
                            <?$label = GetMessage('BXMOD_AUTH_RESTORE_EMAIL')?>
                            <?$placeholder = GetMessage('BXMOD_AUTH_RESTORE_EMAIL_PLACEHOLDER')?>
                        <?else:?>
                            <?$label = GetMessage('BXMOD_AUTH_RESTORE_PHONE')?>
                            <?$placeholder = GetMessage('BXMOD_AUTH_RESTORE_PHONE_PLACEHOLDER')?>
                        <?endif?>
                        <label><?=$label?>:</label>
                        <input type="text" name="bxmodAuthRestoreEmail" value="<?=$arResult["USER_LOGIN"]?>" placeholder="<?=$placeholder?>">

                        <?if( $arParams["OPTIONS"]["EMAIL_RESTORE_CAPTCHA"] == "Y" ):?>
                        <div class="bxmodAuthCaptchaBlock taCaptchaRestore">
                            <div class="bxmodAuthCaptchaInp">
                                <label><?=GetMessage('BXMOD_AUTH_ENTER_CODE')?>:</label>
                                <input type="hidden" name="bxmodAuthRestoreCaptchaSid" value="<?=$arResult["CAPTCHA_RESTORE_CODE"]?>" class="captchaSid">
                                <input type="text" name="bxmodAuthRestoreCaptcha" placeholder="<?=GetMessage('BXMOD_AUTH_ENTER_CODE_PLACEHOLDER')?>" class="captchaWord">
                            </div>
                            <div class="bxmodAuthCaptchaImg">
                                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_RESTORE_CODE"]?>" alt="<?=GetMessage('BXMOD_AUTH_YOU_ARE_ROBOT')?>" class="captcha">
                            </div>
                            <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_CAPTCHA_REFRESH_TITLE')?>"><?=GetMessage('BXMOD_AUTH_CAPTCHA_REFRESH')?></a>
                        </div>
                        <?endif?>
                        <div class="bxmodAuthSMSLimit hidden" id="bxmodAuthSMSLimit<?=rand()?>"></div>
                        <div class="bxmodAuthDesc">
                            <?=GetMessage('BXMOD_AUTH_RESTORE_PASS_DESC')?>
                        </div>
                        <div class="bxmodAuthButtons">
                            <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_CANCEL_2')?>" class="bxmodAuthToLogin"><?=GetMessage('BXMOD_AUTH_CANCEL')?></a>
                            <button type="button" title="<?=GetMessage('BXMOD_AUTH_RESTORE')?>" value="<?=GetMessage('BXMOD_AUTH_ONE_MOMENT')?>" class="bxmodAuthRestoreButton"><?=GetMessage('BXMOD_AUTH_RESTORE')?></button>
                        </div>
                    </form>
                    <form class="bxmodAuthConfirm">
                        <div class="bxmodAuthTitle phone"><?=GetMessage('BXMOD_AUTH_PHONE_CONFIRM_TITLE')?></div>
                        <div class="bxmodAuthTitle email"><?=GetMessage('BXMOD_AUTH_EMAIL_CONFIRM_TITLE')?></div>
                        <div class="bxmodAuthConfirmDesc phone"><?=GetMessage('BXMOD_AUTH_PHONE_CONFIRM_DESC')?></div>
                        <div class="bxmodAuthConfirmDesc email"><?=GetMessage('BXMOD_AUTH_EMAIL_CONFIRM_DESC')?></div>
                        <label><?=GetMessage('BXMOD_AUTH_CONFIRM_CODE')?></label>
                        <input type="hidden" name="bxmodAuthConfirmLogin" value="">
                        <input type="text" name="bxmodAuthConfirmCode" value="" placeholder="<?=GetMessage('BXMOD_AUTH_CONFIRM_CODE_PLACEHOLDER')?>">
                        <div class="bxmodAuthButtons">
                            <button type="button" title="<?=GetMessage('BXMOD_AUTH_CONFIRM')?>" value="<?=GetMessage('BXMOD_AUTH_ONE_MOMENT')?>" class="bxmodAuthConfirmButton"><?=GetMessage('BXMOD_AUTH_CONFIRM')?></button>
                        </div>
                    </form>
                    <div class="bxmodAuthRestoreSend">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_RESTORE_TITLE')?></div>
                        <?if( isset( $arResult["RESTORE_PASS"]["LOGIN"] ) ):?>
                        <div class="bxmodAuthRestoreSendEmail bxmodAuthRestoreSendPhone">
                            <?=GetMessage('BXMOD_AUTH_RESTORE_INSTRUCTIONS')?>
                        </div>
                        <?else:?>
                        <div class="bxmodAuthRestoreSendEmail">
                            <?=GetMessage('BXMOD_AUTH_RESTORE_EMAIL_INSTRUCTIONS')?>
                        </div>
                        <div class="bxmodAuthRestoreSendPhone">
                            <?=GetMessage('BXMOD_AUTH_RESTORE_PHONE_INSTRUCTIONS')?>
                        </div>
                        <?endif?>
                        <form class="bxmodAuthSetPass">
                            <?if( isset( $arResult["RESTORE_PASS"]["LOGIN"] ) ):?>
                                <input type="hidden" name="bxmodAuthRestoreLogin" value="<?=$arResult["RESTORE_PASS"]["LOGIN"]?>">
                            <?endif?>
                            <div>
                                <label><?=GetMessage('BXMOD_AUTH_RESTORE_CODE')?>:</label>
                                <input type="text" name="bxmodAuthRestoreCode" value="<?=isset( $arResult["RESTORE_PASS"]["CHECKWORD"] ) ? $arResult["RESTORE_PASS"]["CHECKWORD"] : ""?>" placeholder="<?=GetMessage('BXMOD_AUTH_RESTORE_CODE_TITLE')?>">
                            </div>
                            <div>
                                <label><?=GetMessage('BXMOD_AUTH_RESTORE_NEW_PASS')?>:</label>
                                <input type="password" name="bxmodAuthRestorePassword" value="" placeholder="<?=GetMessage('BXMOD_AUTH_RESTORE_NEW_PASS_TITLE')?>">
                            </div>
                            <div>
                                <label><?=GetMessage('BXMOD_AUTH_RESTORE_NEW_REPASS')?>:</label>
                                <input type="password" name="bxmodAuthRestoreRePassword" value="" placeholder="<?=GetMessage('BXMOD_AUTH_RESTORE_NEW_REPASS_TITLE')?>">
                            </div>
                            <div class="bxmodAuthButtons">
                                <a href="javascript:;" title="<?=GetMessage('BXMOD_AUTH_CANCEL_2')?>" class="bxmodAuthToLogin"><?=GetMessage('BXMOD_AUTH_CANCEL')?></a>
                                <button type="button" title="<?=GetMessage('BXMOD_AUTH_RESTORE_SET_PASS')?>" value="<?=GetMessage('BXMOD_AUTH_ONE_MOMENT')?>" class="bxmodAuthSetPassButton"><?=GetMessage('BXMOD_AUTH_RESTORE_SET_PASS')?></button>
                            </div>
                        </form>
                    </div>
                    <div class="bxmodAuthSuccessRegister">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_SUCCESS_REGISTER_TITLE')?></div>
                        <p class="bxmodAuthMess"><?=GetMessage('BXMOD_AUTH_SUCCESS_MESS')?> <span id="bxmodAuthReloadTimer<?=rand()?>"><?=$arParams["SUCCESS_RELOAD_TIME"]?></span> <?=GetMessage('BXMOD_AUTH_SUCCESS_SEC')?></p>
                        <p class="bxmodAuthMess"><a href="javascript:;" class="taSuccess" title="<?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?>"><?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?></a></p>
                    </div>
                    <div class="bxmodAuthSuccessLogin">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_SUCCESS_AUTH_TITLE')?></div>
                        <p class="bxmodAuthMess"><?=GetMessage('BXMOD_AUTH_SUCCESS_MESS')?> <span id="bxmodAuthReloadTimer<?=rand()?>"><?=$arParams["SUCCESS_RELOAD_TIME"]?></span> <?=GetMessage('BXMOD_AUTH_SUCCESS_SEC')?></p>
                        <p class="bxmodAuthMess"><a href="javascript:;" class="taSuccess" title="<?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?>"><?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?></a></p>
                    </div>
                    <div class="bxmodAuthSuccessRestore">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_SUCCESS_RESTORE_TITLE')?></div>
                        <p class="bxmodAuthMess"><?=GetMessage('BXMOD_AUTH_SUCCESS_MESS')?> <span id="bxmodAuthReloadTimer<?=rand()?>"><?=$arParams["SUCCESS_RELOAD_TIME"]?></span> <?=GetMessage('BXMOD_AUTH_SUCCESS_SEC')?></p>
                        <p class="bxmodAuthMess"><a href="javascript:;" class="taSuccess" title="<?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?>"><?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?></a></p>
                    </div>
                    <div class="bxmodAuthAllError">
                        <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_UNKNOWN_ERROR_TITLE')?></div>
                        <p class="bxmodAuthMess"><?=GetMessage('BXMOD_AUTH_UNKNOWN_ERROR_DESC')?></p>
                        <p class="bxmodAuthMess"><a href="javascript:;" class="taSuccess" title="<?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?>"><?=GetMessage('BXMOD_AUTH_SUCCESS_REFRESH')?></a></p>
                    </div>
                </div>
            </div>
        </div>
    <?endif?>
    <?if( $arParams["OPTIONS"]["USE_SOCIAL"] == "Y" ):?>
        <?if( $arResult["AUTH_SERVICES"] && !empty( $arResult["AUTH_SERVICES"] ) ):?>
        <div class="bxmodAuthRight">
            <div class="bxmodAuthTitle"><?=GetMessage('BXMOD_AUTH_SOC_SERV_TITLE')?></div>
            <div class="bxmodAuthSocial">
                <?foreach ( $arResult["AUTH_SERVICES"] AS $socServ ):?>
                    <a href="<?=$socServ["LINK"]?>" title="<?=$socServ["NAME"]?>" class="<?=$socServ["TRANSLIT"]?>"></a>
                <?endforeach?>
            </div>
            <div class="bxmodAuthDesc" style="text-align: center;">
                <?=GetMessage('BXMOD_AUTH_SOC_SERV_DESC')?>
            </div>
        </div>
        <?endif?>
    <?endif?>
    </div>
    <a href="javascript:;" class="bxmodAuthShowLink" title="<?=GetMessage('BXMOD_AUTH_LOGIN_REGISTER_LINK_TITLE')?>"><?=GetMessage('BXMOD_AUTH_LOGIN_REGISTER_LINK')?></a>
<?else:?>
    <p><?=$arResult["USER_LOGIN"]?></p>
    <a href="<?=$arResult["LOGOUT_LINK"]?>" title="<?=GetMessage('BXMOD_AUTH_LOGOUT_LINK_TITLE')?>"><?=GetMessage('BXMOD_AUTH_LOGOUT_LINK')?></a>
<?endif?>