<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? foreach($arResult['ELEMENTS'] as $i => $element) { ?>

    <? if($i == 0) { ?>
        <div class="col-md-12 animate-box needs__row-items">
    <? } ?>

    <? if($i != 0 && $i % 4 == 0) { ?>
        </div>
        <div class="col-md-12 animate-box needs__row-items">
    <? } ?>

        <div class="col-md-3 animate-box">
            <div class="col-md-12 animate-box">
                <h3><?=$element["NAME"]?></h3>
                <h5>Мы собрали <?=$element["PROPERTIES"]["PROGRESS"]["VALUE"]?>%</h5>
                <div class="star_wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21">
                        <defs>
                            <style>
                                #fig_2_1 {
                                    fill: #2f803f;
                                }

                                .cls-1, .cls-2 {
                                    fill-rule: evenodd;
                                }

                                .cls-2 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path id="fig_2_1" data-name="fig_2_1" class="cls-1" d="M11.709,1.191l2.718,5.223,6.078,0.838a1.031,1.031,0,0,1,.611,1.782l-4.4,4.065,1.038,5.74a1.091,1.091,0,0,1-1.6,1.1l-5.437-2.71-5.437,2.71a1.091,1.091,0,0,1-1.6-1.1L4.724,13.1l-4.4-4.065A1.031,1.031,0,0,1,.936,7.251l6.078-.838L9.733,1.191A1.129,1.129,0,0,1,11.709,1.191Z"/>
                        <path id="fig_2_2" data-name="fig_2_2" class="cls-2" d="M10.983,1.855l2.355,4.989S10.119,6.955,10.983,1.855Z"/>
                        <path id="fig_2_3" data-name="fig_2_3" class="cls-2" d="M14.5,7.606l5.712,0.518S18.721,10.846,14.5,7.606Z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21">
                        <defs>
                            <style>
                                #fig_3_1 {
                                    fill: #4ca142;
                                }

                                .cls-1, .cls-2 {
                                    fill-rule: evenodd;
                                }

                                .cls-2 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path id="fig_3_1" data-name="fig_3_1" class="cls-1" d="M11.709,1.191l2.718,5.223,6.078,0.838a1.031,1.031,0,0,1,.611,1.782l-4.4,4.065,1.038,5.74a1.091,1.091,0,0,1-1.6,1.1l-5.437-2.71-5.437,2.71a1.091,1.091,0,0,1-1.6-1.1L4.724,13.1l-4.4-4.065A1.031,1.031,0,0,1,.936,7.251l6.078-.838L9.733,1.191A1.129,1.129,0,0,1,11.709,1.191Z"/>
                        <path id="fig_3_2" data-name="fig_3_2" class="cls-2" d="M10.983,1.855l2.355,4.989S10.119,6.955,10.983,1.855Z"/>
                        <path id="fig_3_3" data-name="fig_3_3" class="cls-2" d="M14.5,7.606l5.712,0.518S18.721,10.846,14.5,7.606Z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21">
                        <defs>
                            <style>
                                #fig_4_1 {
                                    fill: #e62435;
                                }

                                .cls-1, .cls-2 {
                                    fill-rule: evenodd;
                                }

                                .cls-2 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path id="fig_4_1" data-name="fig_4_1" class="cls-1" d="M11.709,1.191l2.718,5.223,6.078,0.838a1.031,1.031,0,0,1,.611,1.782l-4.4,4.065,1.038,5.74a1.091,1.091,0,0,1-1.6,1.1l-5.437-2.71-5.437,2.71a1.091,1.091,0,0,1-1.6-1.1L4.724,13.1l-4.4-4.065A1.031,1.031,0,0,1,.936,7.251l6.078-.838L9.733,1.191A1.129,1.129,0,0,1,11.709,1.191Z"/>
                        <path id="fig_4_2" data-name="fig_4_2" class="cls-2" d="M10.983,1.855l2.355,4.989S10.119,6.955,10.983,1.855Z"/>
                        <path id="fig_4_3" data-name="fig_4_3" class="cls-2" d="M14.5,7.606l5.712,0.518S18.721,10.846,14.5,7.606Z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21">
                        <defs>
                            <style>
                                #fig_5_1 {
                                    fill: #4180c1;
                                }

                                .cls-1, .cls-2 {
                                    fill-rule: evenodd;
                                }

                                .cls-2 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path id="fig_5_1" data-name="fig_5_1" class="cls-1" d="M11.709,1.191l2.718,5.223,6.078,0.838a1.031,1.031,0,0,1,.611,1.782l-4.4,4.065,1.038,5.74a1.091,1.091,0,0,1-1.6,1.1l-5.437-2.71-5.437,2.71a1.091,1.091,0,0,1-1.6-1.1L4.724,13.1l-4.4-4.065A1.031,1.031,0,0,1,.936,7.251l6.078-.838L9.733,1.191A1.129,1.129,0,0,1,11.709,1.191Z"/>
                        <path id="fig_5_2" data-name="fig_5_2" class="cls-2" d="M10.983,1.855l2.355,4.989S10.119,6.955,10.983,1.855Z"/>
                        <path id="fig_5_3" data-name="fig_5_3" class="cls-2" d="M14.5,7.606l5.712,0.518S18.721,10.846,14.5,7.606Z"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21">
                        <defs>
                            <style>
                                #fig_6_1 {
                                    fill: #50b6e8;
                                }

                                .cls-1, .cls-2 {
                                    fill-rule: evenodd;
                                }

                                .cls-2 {
                                    fill: #fff;
                                }
                            </style>
                        </defs>
                        <path id="fig_6_1" data-name="fig_6_1" class="cls-1" d="M11.709,1.191l2.718,5.223,6.078,0.838a1.031,1.031,0,0,1,.611,1.782l-4.4,4.065,1.038,5.74a1.091,1.091,0,0,1-1.6,1.1l-5.437-2.71-5.437,2.71a1.091,1.091,0,0,1-1.6-1.1L4.724,13.1l-4.4-4.065A1.031,1.031,0,0,1,.936,7.251l6.078-.838L9.733,1.191A1.129,1.129,0,0,1,11.709,1.191Z"/>
                        <path id="fig_6_2" data-name="fig_6_2" class="cls-2" d="M10.983,1.855l2.355,4.989S10.119,6.955,10.983,1.855Z"/>
                        <path id="fig_6_3" data-name="fig_6_3" class="cls-2" d="M14.5,7.606l5.712,0.518S18.721,10.846,14.5,7.606Z"/>
                    </svg>
                </div>
                <div class="progress-wrap ftco-animate">
                    <div class="progress">
                        <div class="progress-bar color-1" role="progressbar" aria-valuenow="<?=$element["PROGRESS"][0]?>"
                             aria-valuemin="0" aria-valuemax="100" style="width:<?=$element["PROGRESS"][0]?>%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animate-box">
                <div class="progress-wrap ftco-animate">
                    <div class="progress">
                        <div class="progress-bar color-2" role="progressbar" aria-valuenow="<?=$element["PROGRESS"][1]?>"
                             aria-valuemin="0" aria-valuemax="100" style="width:<?=$element["PROGRESS"][1]?>%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animate-box">
                <div class="progress-wrap ftco-animate">
                    <div class="progress">
                        <div class="progress-bar color-3" role="progressbar" aria-valuenow="<?=$element["PROGRESS"][2]?>"
                             aria-valuemin="0" aria-valuemax="100" style="width:<?=$element["PROGRESS"][2]?>%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="harvest_icon">
                <? if($element['FULL']) { ?>
                    <img src="<?=$element['PROPERTIES']['IMG_100']['VALUE'] ? \CFile::GetPath($element['PROPERTIES']['IMG_100']['VALUE']) : '/dobrolap/images/thanks_ic.png'?> " alt="спасибо за заботу"  />
                <? } else { ?>
                    <img src="<?=$element['PROPERTIES']['IMG']['VALUE'] ? \CFile::GetPath($element['PROPERTIES']['IMG']['VALUE']) : '/dobrolap/images/more_ic.png'?>" alt="нужно еще"  />
                <? } ?>
            </div>
        </div>

<? } ?>

</div> <!-- col-md-12 -->



