<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/**
 * Бренды: 404
 *
 * @updated: 21.12.2017
 */

$this->setFrameMode(false);

@define('ERROR_404', 'Y');
\CHTTP::SetStatus('404 Not Found');
