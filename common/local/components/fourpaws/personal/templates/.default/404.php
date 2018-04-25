<? use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;

if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Бренды: 404
 *
 * @updated: 25.12.2017
 */

$this->setFrameMode(false);
CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');

$APPLICATION->SetTitle('404 Not Found');
/** @var MainTemplate $template */
if (!isset($template) || !($template instanceof MainTemplate)) {
    $template = MainTemplate::getInstance(Application::getInstance()->getContext());
}

?>
<div class="b-container--error">
    <div class="b-error-page">
        <img src="/static/build/images/content/404.png">
        <p class="b-title b-title--h1">Такой страницы нет</p>
        <p>Проверьте правильность адреса, воспользуйтесь поиском или начните с главной страницы</p>
        <a href="/">Перейти на главную страницу</a>
    </div>
</div>
<?php /** @todo где-то не закрыт див - но не нашел где */?>
</div>