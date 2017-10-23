<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

if ($arResult['MODE'] === FourPawsUserComponent::MODE_FORM) { ?>
    
    <form name="login_form">
        <input name="login" type="text" placeholder="Логин"><br>
        <input name="password" type="password" placeholder="Пароль"><br>
        <input type="submit" value="Войти">
    </form>
    <? foreach ($arResult['socialServices'] as $service) { ?>
        <?= $service['FORM_HTML'] ?>
    <? } ?>
    <div id="result">
    
    </div>
    
    <script>
        $(function () {
            $('input[type="submit"').on('click',
                                        function (e) {
                                            e.preventDefault();
                
                                            $.ajax({
                                                       success: function (data) {
                                                           console.info(data);
                                                           $('#result').html(data);
                                                       },
                                                       url:     '/ajax/user/auth/login/',
                                                       data:    $(this).parents('form').serialize(),
                                                       type:    'post'
                                                   });
                
                                            return false;
                                        })
        })
    </script>
    <?php
    
} else {
    /**
     * @var $user \FourPaws\BitrixOrm\Model\User
     */
    $user = $arResult['user'];
    
    ?>
    <div>
        <b><?= $user->getName() ?></b> <?= $user->getSecondName() ?> <?= $user->getLastName() ?>
        <br>
        <a href="?logout=yes">Выйти</a>
    </div>
<?php }
