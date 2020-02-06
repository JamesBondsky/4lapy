<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>

<div id="DPD_pvz_popup" style="display: none;">
	<div id="DPD_pvz" data-component-params='<?= json_encode($arParams) ?>' data-component-template="<?= $templateName ?>">
		<div id="DPD_map">
			<? $APPLICATION->IncludeComponent('bitrix:map.yandex.view', '.default', array(
				'INIT_MAP_TYPE' => 'MAP',
				'MAP_WIDTH'     => '100%',
				'MAP_HEIGHT'    => '500',
				'CONTROLS'      => array('ZOOM'),
				'OPTIONS'       => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING'),
				'MAP_ID'        => 'dpd_map',
				'MAP_DATA'      => serialize($arResult['MAP_DATA']),
				'ONMAPREADY'    => 'DpdPickupMap',
			), $component, array('HIDE_ICONS' => 'Y')) ?>
		</div>

		<div id="DPD_info">
			<div id="DPD_sign" style="background-image: url(<?= $templateFolder ?>/images/logo.png)"><?= GetMessage('IPOLH_DPD_PICKUP_POINTS') ?></div>
			
			<div id="DPD_delivInfo_PVZ">
				<a href="javascript:void(0);" class="DPD_arrow"></a>

				<div>
					<?= GetMessage('IPOLH_DPD_PICKUP_COST') ?>
					<span id="DPD_pPrice"><?= GetMessage('IPOLH_DPD_PICKUP_COST_TEXT', array('#PRICE#' => $arResult['TARIFFS']['PICKUP']['COST'])) ?></span>, 
					
					<?= GetMessage('IPOLH_DPD_PICKUP_PERIOD_SM') ?> 
					<span id="DPD_pDate"><?= GetMessage('IPOLH_DPD_PICKUP_PERIOD_TEXT', array('#DAYS#' => $arResult['TARIFFS']['PICKUP']['DAYS'])) ?></span>
				</div>
			</div>

			<div id="DPD_modController" class="<?= count($arResult['TERMINAL_TYPES']) > 1 ? '' : 'dpd-hidden' ?>">
				<div class="DPD_mC_block active" 
				     id="DPD_mC_ALL" 
				     data-type="all"
				><?= GetMessage('IPOLH_DPD_PICKUP_TEMINAL_TYPE_ALL') ?></div>
				
				<div class="DPD_mC_block <?= in_array(GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_PVP_KEY'), $arResult['TERMINAL_TYPES']) ? '' : 'dpd-hidden'?>" 
				     id="DPD_mC_PVZ" 
				     data-type="<?= GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_PVP_KEY') ?>"
				><?= GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_PVP') ?></div>
				
				<div class="DPD_mC_block <?= in_array(GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_POSTOMAT_KEY'), $arResult['TERMINAL_TYPES']) ? '' : 'dpd-hidden'?>" 
				     id="DPD_mC_POSTOMAT" 
				     data-type="<?= GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_POSTOMAT_KEY') ?>"
				><?= GetMessage('IPOLH_DPD_PICKUP_TERMINAL_TYPE_POSTOMAT') ?></div>
			</div>

			<div>
				<div id="DPD_wrapper">
					<?php foreach ($arResult['TERMINALS'] as $arTerminal) { ?>
						<p class="DPD_terminalSelect" data-terminal-code="<?= $arTerminal['ID'] ?>" data-terminal-address="<?= htmlspecialchars($arTerminal['ADDRESS_FULL']) ?>"><?= $arTerminal['NAME'] ?></p>
					<?php }?>
				</div>
			</div>

			<div id="DPD_ten"></div>
		</div>

		<div id="DPD_head">
			<div id="DPD_logo">
				<a href="http://ipolh.com" target="_blank"></a>
			</div>
		</div>

		<div id="DPD_mask"></div>
	</div>
</div>