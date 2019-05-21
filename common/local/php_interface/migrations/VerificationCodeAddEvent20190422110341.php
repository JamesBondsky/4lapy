<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class VerificationCodeAddEvent20190422110341 extends SprintMigrationBase
{

    protected $description = "Добавление почтового шаблона для отправки сообщений об ошибках в мобильном приложении";

    public function up()
    {
        $helper = new HelperManager();

        $siteId = 's1';
        $lang = 'ru';

        $helper->Event()->addEventTypeIfNotExists(
            'VerificationCode',
            [
                'LID' => $lang,
                'NAME' => 'Код подтверждения',
                'DESCRIPTION' => '#USER_EMAIL# - email пользователя
#CODE# - проверочный код
#TEXT# - основной текст в письме',
            ]
        );

        $helper->Event()->addEventMessageIfNotExists(
            'VerificationCode',
            [
                'LID' => $siteId,
                'LANGUAGE_ID' => $lang,
                'EMAIL_TO' => '#USER_EMAIL#',
                //'BCC' => '',
                'SUBJECT' => 'Помощь с вашим аккаунтом',
                'BODY_TYPE' => 'html',
                'MESSAGE' => '<style type="text/css">
html { -webkit-text-size-adjust:none; -ms-text-size-adjust: none;}
@media only screen and (max-device-width: 660px), only screen and (max-width: 660px) { 
	.table660{
		width: 100% !important;
	}
	.mob_50 {
		width: 50% !important;
	}
	.mob_100{
		width:100% !important;
	}
	.mob_center{
		text-align: center !important;
	}
	.mob_center_bl{
		float:none !important;
		display: block !important;
		margin: 0 auto;
	}
	.mob_hidden{
		display:none !important;
	}
}
@media only screen and (max-width: 660px) { 
	.table660{
		width: 100% !important;
	}
}
.mob_link a{
	text-decoration:none;
	color:#b1bac3;
}
.preheader{
	display:none !important;
}
</style> <style type="text/css">
	@-ms-viewport{width:device-width}
</style> <!--[if (gte mso 9)|(IE)]>
<style type="text/css">
.not_for_outlook {
mso-hide: all !important;
display: none !important;
font-size: 0;
max-height: 0;
line-height: 0;
mso-hide: all;
}
</style>
<![endif]-->
<div id="mailsub" class="notification" align="center" style="word-break:normal;-webkit-text-size-adjust:none; -ms-text-size-adjust: none;line-height: normal;">
	<table width="100%" cellspacing="0" cellpadding="0" style="line-height: normal;">
	<tbody>
	<tr>
		<td align="center" bgcolor="#ffffff">
			 <!--[if (gte mso 9)|(IE)]>
<table width="660" border="0" cellspacing="0" cellpadding="0"><tr><td>
<![endif]-->
			<div>
				 <!--head--><!--head END--><!--main --><!--main END-->
				<table cellspacing="0" cellpadding="0" class="table660" width="100%" style="max-width: 660px;min-width:300px;">
				<tbody>
				<tr>
					<td align="center">
						 <!--[if !mso]><!-->
						<div class="preheader" style="font-size:0px;color:transparent;opacity:0;">
							Код подтверждения
						</div>
						 <!--<![endif]-->
					</td>
				</tr>
				<tr>
					<td align="center">
						 <!-- padding -->
						<div style="height: 15px; line-height:15px; font-size:8px;">
							&nbsp;
						</div>
						<table width="91%" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="right">
								<div style="line-height: 14px;">
 <a href="*[link_viewinbrowser]*" target="_blank" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 12px; color: #d0d0d0; line-height: 14px; text-decoration: underline;">
									Открыть в браузере </a>
								</div>
							</td>
						</tr>
						</tbody>
						</table>
						 <!-- padding -->
						<div style="height: 10px; line-height:10px; font-size:8px;">
							&nbsp;
						</div>
						 <!-- logo -->
						<table width="91%" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="left" valign="top" style="font-size: 0px; line-height: 0px;">
								<!-- 

	Item -->
								<div style="display: inline-block; vertical-align: top; width: 100%; max-width: 436px; font-size: 14px; line-height: 18px;">
									<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
									<tbody>
									<tr>
										<td align="left" valign="top" style="font-size: 14px; line-height: 18px;">
 <a href="https://4lapy.ru/" target="_blank" style="color: #ee7202; font-family: Arial, Helvetica, sans-serif; font-size: 20px; font-weight: bold;"> <img width="248" alt="ЧЕТЫРЕ ЛАПЫ" src="http://img.expertsender.ru/expertsender_ru/4lapy/190718/img/logo.png" height="48" border="0" style="display: block;"></a>
										</td>
									</tr>
									</tbody>
									</table>
								</div>
								<!-- Item END--><!--[if (gte mso 9)|(IE)]>
	</td>
	<td valign="top" width="164">
	<![endif]--><!-- 

	Item -->
								<div style="display: inline-block; vertical-align: top; width: 164px; font-size: 14px; line-height: 18px;">
									<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
									<tbody>
									<tr>
										<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
											 <!-- padding -->
											<div style="height: 10px; line-height:10px; font-size:8px;">
												&nbsp;
											</div>
											<table width="100%" cellspacing="0">
											<tbody>
											<tr>
												<td align="left" valign="middle" width="30">
 <a href="tel:88007700022" target="_blank" style="color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="19" src="http://img.expertsender.ru/expertsender_ru/4lapy/190718/img/phone.png" height="28" alt="" border="0" style="display: block;"></a>
												</td>
												<td align="left" valign="middle">
													<div style="line-height: 17px;">
 <a href="tel:88007700022" target="_blank" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 15px; color: #303030; line-height: 17px; text-decoration: none;">
														8&nbsp;<span style="color:#ffffff;font-size: 0px;line-height: 0;">‌</span>(800)&nbsp;<span style="color:#ffffff;font-size: 0px;line-height: 0;">‌</span>770<span style="color:#ffffff;font-size: 0px;line-height: 0;">‌</span>-00<span style="color:#ffffff;font-size: 0px;line-height: 0;">‌</span>-22 </a>
													</div>
												</td>
											</tr>
											</tbody>
											</table>
										</td>
									</tr>
									</tbody>
									</table>
								</div>
								<!-- 

Item END-->
							</td>
						</tr>
						</tbody>
						</table>
						 <!-- logo END --> <!-- menu -->
						<table width="100%" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="center">
								<table width="91%" cellspacing="0" cellpadding="0">
								<tbody>
								<tr>
									<td align="center" style="font-size: 0px;line-height: 0px;">
										<!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:300px; font-size: 14px; line-height: 18px;">
											<table width="300" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 15px; line-height: 15px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#ee7203; font-weight: bold;"><a href="https://4lapy.ru/shares/" target="_blank" style="color: #ee7203; font-weight: bold; text-decoration:none;">Акции</a></span>
															</div>
														</td>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#303030; font-weight: bold;"><a href="https://4lapy.ru/catalog/sobaki/" target="_blank" style="color: #303030; font-weight: bold; text-decoration:none;">Собаки</a></span>
															</div>
														</td>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#303030; font-weight: bold;"><a href="https://4lapy.ru/catalog/koshki/" target="_blank" style="color: #303030; font-weight: bold; text-decoration:none;">Кошки</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END--><!--[if (gte mso 9)|(IE)]>
		</td>
		<td valign="top" width="300">
	<![endif]--><!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:300px; font-size: 14px; line-height: 18px;">
											<table width="300" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 15px; line-height: 15px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#303030; font-weight: bold;"><a href="https://4lapy.ru/catalog/gryzuny-i-khorki/" target="_blank" style="color: #303030; font-weight: bold; text-decoration:none;">Грызуны</a></span>
															</div>
														</td>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#303030; font-weight: bold;"><a href="https://4lapy.ru/catalog/ryby/" target="_blank" style="color: #303030; font-weight: bold; text-decoration:none;">Рыбы</a></span>
															</div>
														</td>
														<td align="center" valign="top" width="100">
															<div style="line-height: 18px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 16px; line-height: 18px; color:#303030; font-weight: bold;"><a href="https://4lapy.ru/catalog/ptitsy/" target="_blank" style="color: #303030; font-weight: bold; text-decoration:none;">Птицы</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END-->
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 15px; line-height: 15px; font-size: 8px;">
									&nbsp;
								</div>
							</td>
						</tr>
						</tbody>
						</table>
						 <!-- menu END -->
						<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
						<tbody>
						<tr>
							<td align="center">
								 <!-- padding -->
								<div style="height: 30px; line-height:30px; font-size:28px;">
									&nbsp;
								</div>
								<table width="84%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
								<tbody>
								<tr>
									<td align="left">
										<div style="line-height: 24px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 19px; color: #ee7202; line-height: 24px; font-weight: bold;">
											Здравствуйте! </span>
										</div>
										 <!-- padding -->
										<div style="height: 10px; line-height:10px; font-size:8px;">
											&nbsp;
										</div>
										<div style="line-height: 20px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #303030; line-height: 20px;">
											#TEXT# </span>
										</div>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 24px; line-height:24px; font-size:8px;">
									&nbsp;
								</div>
								<table width="262" cellspacing="0" cellpadding="0">
								<tbody>
								<tr>
									<td align="center" valign="middle" height="58" style="border-width: 1px; border-style: solid; border-color: #ee7202;">
										<table width="130" cellspacing="0" cellpadding="0">
										<tbody>
										<tr>
											<td align="left" valign="middle" width="40">
												<div style="line-height: 16px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #303030; line-height: 16px;">
													Код: </span>
												</div>
											</td>
											<td align="left" valign="middle">
												<div style="line-height: 26px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 24px; color: #ee7202; line-height: 26px; font-weight: bold;">
													#CODE# </span>
												</div>
											</td>
										</tr>
										</tbody>
										</table>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 36px; line-height:36px; font-size:8px;">
									&nbsp;
								</div>
							</td>
						</tr>
						<tr>
							<td align="center" style="border-top-width:1px; border-top-style:solid; border-top-color:#dddddd;">
								 <!-- padding -->
								<div style="height: 29px; line-height:29px; font-size:8px;">
									&nbsp;
								</div>
								<table width="84%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
								<tbody>
								<tr>
									<td align="left">
										<div style="line-height: 21px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #bebebe; line-height: 21px;">
											Если вы&nbsp;не&nbsp;отправляли запрос, сообщите в&nbsp;нашу службу поддержки. </span>
										</div>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 30px; line-height: 30px; font-size: 8px;">
									&nbsp;
								</div>
								 <!-- block -->
								<table width="91%" cellpadding="0" cellspacing="0" style="max-width: 91%; min-width: 91%;">
								<tbody>
								<tr>
									<td align="center" style="border-width: 1px; border-style: solid; border-color: #ff6d34;">
										<table width="90%" cellspacing="0" cellpadding="0">
										<tbody>
										<tr>
											<td align="center" valign="top" style="font-size: 0px; line-height: 0px;">
												<!-- 

				Item -->
												<div style="display: inline-block; vertical-align: top; width: 250px; font-size: 14px; line-height: 18px;">
													<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
													<tbody>
													<tr>
														<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
															<table width="250" cellspacing="0" cellpadding="0">
															<tbody>
															<tr>
																<td align="center">
																	 <!-- padding -->
																	<div style="height: 21px; line-height: 21px; font-size: 8px;">
																		&nbsp;
																	</div>
																	<table width="100%" cellpadding="0" cellspacing="0">
																	<tbody>
																	<tr>
																		<td align="center">
																			<div style="line-height: 20px;">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 18px; color: #ff6d34; line-height: 20px; font-weight: bold;">
																				Покупайте в приложении</span>
																			</div>
																		</td>
																	</tr>
																	</tbody>
																	</table>
																	 <!-- padding -->
																	<div style="height: 15px; line-height: 15px; font-size: 8px;">
																		&nbsp;
																	</div>
																	<table width="100%" cellspacing="0" cellpadding="0">
																	<tbody>
																	<tr>
																		<td align="center" width="125">
																			<div style="line-height: 14px;">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 12px; color: #48443e; line-height: 14px; ">
																				Четыре Лапы для <br>
																				iPhone</span>
																			</div>
																			 <!-- padding -->
																			<div style="height: 5px; line-height: 5px; font-size: 3px;">
																				&nbsp;
																			</div>
																			<table width="119" cellpadding="0" cellspacing="0">
																			<tbody>
																			<tr>
																				<td>
 <a href="https://itunes.apple.com/us/app/%D1%87%D0%B5%D1%82%D1%8B%D1%80%D0%B5-%D0%BB%D0%B0%D0%BF%D1%8B-%D0%B7%D0%BE%D0%BE%D0%BC%D0%B0%D0%B3%D0%B0%D0%B7%D0%B8%D0%BD/id1222315361?mt=8" target="_blank" style="color: #303030; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="119" alt="Загрузить в AppStore" src="http://img.expertsender.ru/expertsender_ru/4lapy/170717/img/appstore.png" height="35" border="0" style="display: block;"></a>
																				</td>
																			</tr>
																			</tbody>
																			</table>
																		</td>
																		<td align="center" width="125">
																			<div style="line-height: 14px;">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 12px; color: #48443e; line-height: 14px; ">
																				Четыре Лапы для <br>
																				Android</span>
																			</div>
																			 <!-- padding -->
																			<div style="height: 5px; line-height: 5px; font-size: 3px;">
																				&nbsp;
																			</div>
																			<table width="102" cellpadding="0" cellspacing="0">
																			<tbody>
																			<tr>
																				<td align="center">
 <a href="https://play.google.com/store/apps/details?id=com.appteka.lapy&hl=ru&ah=T1IjMx6FMe5JHE2j2fOnJewvfSs" target="_blank" style="color: #303030; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="102" alt="Доступно в GooglePlay" src="http://img.expertsender.ru/expertsender_ru/4lapy/130618/2/img/badge_new.png" height="35" border="0" style="display: block;"></a>
																				</td>
																			</tr>
																			</tbody>
																			</table>
																		</td>
																	</tr>
																	</tbody>
																	</table>
																</td>
															</tr>
															</tbody>
															</table>
														</td>
													</tr>
													</tbody>
													</table>
												</div>
												<!-- Item END--><!--[if (gte mso 9)|(IE)]>
				</td>
				<td valign="top" width="269">
				<![endif]--><!-- 

				Item -->
												<div style="display: inline-block; vertical-align: top; width: 269px; font-size: 14px; line-height: 18px;">
													<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
													<tbody>
													<tr>
														<td align="right">
 <img width="100" src="http://img.expertsender.ru/expertsender_ru/4lapy/301018/new_logo.png" height="100" alt="" border="0" style="display: block;">
														</td>
														<td align="right" valign="top" style="font-size: 14px; line-height: 18px;">
															 <!-- padding -->
															<div style="height: 21px; line-height: 21px; font-size: 8px;">
																&nbsp;
															</div>
															<table width="140" cellpadding="0" cellspacing="0">
															<tbody>
															<tr>
																<td align="left" valign="top" width="20">
 <img width="10" src="http://img.expertsender.ru/expertsender_ru/4lapy/300117/cats/img/bull.png" height="15" alt="" border="0" style="display: block;">
																</td>
																<td align="left" valign="top" width="130">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 14px; color: #000000; line-height: 17px;">Удобный поиск товаров</span>
																</td>
															</tr>
															</tbody>
															</table>
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<table width="140" cellpadding="0" cellspacing="0">
															<tbody>
															<tr>
																<td align="left" valign="top" width="20">
 <img width="10" src="http://img.expertsender.ru/expertsender_ru/4lapy/300117/cats/img/bull.png" height="15" alt="" border="0" style="display: block;">
																</td>
																<td align="left" valign="top" width="130">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 14px; color: #000000; line-height: 17px;">Быстрый заказ</span>
																</td>
															</tr>
															</tbody>
															</table>
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<table width="140" cellpadding="0" cellspacing="0">
															<tbody>
															<tr>
																<td align="left" valign="top" width="20">
 <img width="10" src="http://img.expertsender.ru/expertsender_ru/4lapy/300117/cats/img/bull.png" height="15" alt="" border="0" style="display: block;">
																</td>
																<td align="left" valign="top" width="130">
 <span style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 14px; color: #000000; line-height: 17px;">Бонусная карта <br>
																	в телефоне</span>
																</td>
															</tr>
															</tbody>
															</table>
														</td>
													</tr>
													</tbody>
													</table>
												</div>
												<!-- Item END-->
											</td>
										</tr>
										</tbody>
										</table>
										 <!-- padding -->
										<div style="height: 21px; line-height: 21px; font-size: 8px;">
											&nbsp;
										</div>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- block END --> <!-- padding -->
								<div style="height: 30px; line-height:30px; font-size:8px;">
									&nbsp;
								</div>
							</td>
						</tr>
						</tbody>
						</table>
						 <!--footer -->
						<table width="100%" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="center" bgcolor="#585e6a">
								<table width="91%" cellspacing="0" cellpadding="0">
								<tbody>
								<tr>
									<td align="center" style="font-size: 0px;line-height: 0px;">
										<!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:120px; font-size: 14px; line-height: 18px;" class="mob_50">
											<table width="104" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;" class="mob_100">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 25px; line-height: 25px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="middle" height="55">
 <a href="https://4lapy.ru/payment-and-delivery/" target="_blank"> <img width="45" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/icon01.png" height="35" alt="" border="0" style="display: block;"></a>
														</td>
													</tr>
													<tr>
														<td align="center">
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<div style="line-height: 16px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 12px; line-height:16px; color:#ffffff;"><a href="https://4lapy.ru/payment-and-delivery/" target="_blank" style="color: #ffffff;text-decoration:none;">Доставка<br>
																 до двери</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END--><!--[if (gte mso 9)|(IE)]>
		</td>
		<td valign="top" width="120">
	<![endif]--><!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:120px; font-size: 14px; line-height: 18px;" class="mob_50">
											<table width="120" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;" class="mob_100">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 25px; line-height: 25px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="middle" height="55">
 <a href="https://4lapy.ru/payment-and-delivery/" target="_blank"> <img width="37" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/icon02.png" height="37" alt="" border="0" style="display: block;"></a>
														</td>
													</tr>
													<tr>
														<td align="center">
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<div style="line-height: 16px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 12px; line-height:16px; color:#ffffff;"><a href="https://4lapy.ru/payment-and-delivery/" target="_blank" style="color: #ffffff;text-decoration:none;">Оплата<br>
																по факту</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END--><!--[if (gte mso 9)|(IE)]>
		</td>
		<td valign="top" width="120">
	<![endif]--><!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:120px; font-size: 14px; line-height: 18px;" class="mob_50">
											<table width="120" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;" class="mob_100">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 25px; line-height: 25px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="middle" height="55">
 <a href="https://4lapy.ru/podpiska/" target="_blank"> <img width="55" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/icon03.png" height="55" alt="" border="0" style="display: block;"></a>
														</td>
													</tr>
													<tr>
														<td align="center">
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<div style="line-height: 16px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 12px; line-height:16px; color:#ffffff;"><a href="https://4lapy.ru/podpiska/" target="_blank" style="color: #ffffff;text-decoration:none;">Подписка<br>
																на доставку</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END--><!--[if (gte mso 9)|(IE)]>
		</td>
		<td valign="top" width="120">
	<![endif]--><!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:120px; font-size: 14px; line-height: 18px;" class="mob_50">
											<table width="120" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;" class="mob_100">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 25px; line-height: 25px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="middle" height="55">
 <a href="https://4lapy.ru/payment-and-delivery/" target="_blank"> <img width="33" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/icon04.png" height="40" alt="" border="0" style="display: block;"></a>
														</td>
													</tr>
													<tr>
														<td align="center">
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<div style="line-height: 16px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 12px; line-height:16px; color:#ffffff;"><a href="https://4lapy.ru/payment-and-delivery/" target="_blank" style="color: #ffffff;text-decoration:none;">Возврат<br>
																 по желанию</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END--><!--[if (gte mso 9)|(IE)]>
		</td>
		<td valign="top" width="120">
	<![endif]--><!-- 

	Item -->
										<div style="display: inline-block;vertical-align:top; width:120px; font-size: 14px; line-height: 18px;" class="mob_100">
											<table width="120" cellspacing="0" cellpadding="0" align="center" style="border-collapse:collapse;" class="mob_100">
											<tbody>
											<tr>
												<td align="center" valign="top" style="font-size: 14px; line-height: 18px;">
													 <!-- padding -->
													<div style="height: 25px; line-height: 25px; font-size: 8px;">
														&nbsp;
													</div>
													<table width="100%" cellspacing="0" cellpadding="0">
													<tbody>
													<tr>
														<td align="center" valign="middle" height="55">
 <a href="https://4lapy.ru/bonus-program/" target="_blank"> <img width="35" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/icon05.png" height="36" alt="" border="0" style="display: block;"></a>
														</td>
													</tr>
													<tr>
														<td align="center">
															 <!-- padding -->
															<div style="height: 10px; line-height: 10px; font-size: 8px;">
																&nbsp;
															</div>
															<div style="line-height: 16px;">
 <span style="font-family: Arial, Tahoma, Helvetica, sans-serif; font-size: 12px; line-height:16px; color:#ffffff;"><a href="https://4lapy.ru/bonus-program/" target="_blank" style="color: #ffffff;text-decoration:none;">Бонусы<br>
																 за покупку</a></span>
															</div>
														</td>
													</tr>
													</tbody>
													</table>
												</td>
											</tr>
											</tbody>
											</table>
										</div>
										<!-- Item END-->
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 24px; line-height: 24px; font-size: 8px;">
									&nbsp;
								</div>
							</td>
						</tr>
						</tbody>
						</table>
						<table width="100%" cellspacing="0" cellpadding="0">
						<tbody>
						<tr>
							<td align="center">
								 <!-- padding -->
								<div style="height: 30px; line-height: 30px; font-size: 8px;">
									&nbsp;
								</div>
								<table width="179" cellspacing="0" cellpadding="0">
								<tbody>
								<tr>
									<td align="left" valign="middle" width="54">
										<table width="24" cellspacing="0" cellpadding="0">
										<tbody>
										<tr>
											<td align="center">
 <a href="https://vk.com/4lapy_ru" target="_blank" style="color: #b2b2b2; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="24" alt="VK" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/vk.png" height="15" border="0" style="display: block;"></a>
											</td>
										</tr>
										</tbody>
										</table>
									</td>
									<td align="left" valign="middle" width="50">
										<table width="15" cellspacing="0" cellpadding="0">
										<tbody>
										<tr>
											<td align="center">
 <a href="https://ok.ru/chetyre.lapy" target="_blank" style="color: #b2b2b2; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="15" alt="OD" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/od.png" height="25" border="0" style="display: block;"></a>
											</td>
										</tr>
										</tbody>
										</table>
									</td>
									<td align="left" valign="middle" width="50">
										<table width="14" cellspacing="0" cellpadding="0">
										<tbody>
										<tr>
											<td align="center">
 <a href="https://www.facebook.com/4laps" target="_blank" style="color: #b2b2b2; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="14" alt="FB" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/7779/img/fb.png" height="25" border="0" style="display: block;"></a>
											</td>
										</tr>
										</tbody>
										</table>
									</td>
									<td align="left" valign="middle" width="25">
 <a href="https://www.instagram.com/4lapy.ru/" target="_blank" style="color: #b2b2b2; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"> <img width="25" alt="Instagram" src="http://img.expertsender.ru/expertsender_ru/4lapy/190618_2/inst.png" height="25" border="0" style="display: block;"></a>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 30px; line-height: 30px; font-size: 8px;">
									&nbsp;
								</div>
								<table width="65%" cellspacing="0" cellpadding="0" style="min-width: 320px;">
								<tbody>
								<tr>
									<td align="center">
										<div style="line-height: 14px;">
 <span style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #707070; line-height: 14px;">
											Вы&nbsp;получили это письмо, потому оставили свой емейл в&nbsp;магазине розничной сети или интернет-магазине «Четыре лапы». </span>
										</div>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 20px; line-height: 20px; font-size: 8px;">
									&nbsp;
								</div>
								<table width="176" cellspacing="0" cellpadding="0">
								<tbody>
								<tr>
									<td align="left" valign="middle" width="77" height="26" style="border-right-width: 1px; border-right-style: solid; border-right-color: #b4b4b4;">
										<div style="line-height: 13px;">
 <a href="*[link_unsubscribe]*" target="_blank" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 11px; color: #ef7204; line-height: 13px; text-decoration: none;">
											Отписаться </a>
										</div>
									</td>
									<td align="right" valign="middle" width="99" height="26">
										<div style="line-height: 13px;">
 <a href="https://4lapy.ru/feedback/" target="_blank" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 11px; color: #ef7204; line-height: 13px; text-decoration: none;">
											Обратная связь </a>
										</div>
									</td>
								</tr>
								</tbody>
								</table>
								 <!-- padding -->
								<div style="height: 20px; line-height: 20px; font-size: 8px;">
									&nbsp;
								</div>
							</td>
						</tr>
						</tbody>
						</table>
						 <!--footer END-->
					</td>
				</tr>
				</tbody>
				</table>
			</div>
			 <!--[if (gte mso 9)|(IE)]>
</td></tr>
</table>
<![endif]-->
		</td>
	</tr>
	</tbody>
	</table>
</div>',
            ]
        );

    }

    public function down()
    {
        // no down
    }

}
