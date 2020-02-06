<?php
$MESS['BENDERSAY_EXPORTIMPORT_ERROR'] = 'Ошибка AJAX запроса';
$MESS['BENDERSAY_EXPORTIMPORT_RESULT_TEXT'] = 'Файл отправлен';
$MESS['BENDERSAY_EXPORTIMPORT_PROGRESS_BAR'] = 'Экспорт данных';
$MESS['BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT'] = 'Импорт данных';
$MESS['BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT_IZ'] = 'из';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_EMPTY'] = 'Все поля обязательны';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_ERROR'] = 'Экспорт Highload-блока завершился ошибкой';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE'] = 'Экспорт Highload-блока в CSV успешно завершен, CSV можно скачать по ссылке: ';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_JSON'] = 'Экспорт Highload-блока в JSON успешно завершен, JSON можно скачать по ссылке: ';
$MESS['BENDERSAY_EXPORTIMPORT_FINISH_IMPORT'] = 'Импорт Highload-блока из JSON завершен';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND'] = 'Выслать на email: ';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND_BUTTON'] = 'Отправить';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_GETUSERENTITYIMPORT'] = 'Файл для импорта не был указан или не существует на сервере';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SIZE'] = 'Файл для импорта очень большой #file_size#M. Размер доступной оперативной памяти #memory_limit# не позволит импортировать JSON';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_FILE_SIZE_EXP'] = 'Файл экспорта достиг #file_size#M. Размер доступной оперативной памяти #memory_limit# не позволит экспортировать JSON.'
	.' <br>Увеличте <i>memory_limit</i> в php.ini сервера ';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_NOT_KEY'] = 'В импортируемом файле отсутствует обязательный блок с ключом #key#';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_COUNT'] = 'Свойство "items_all_count" не совпадает с реальным количеством записей "items"';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_IMPORT_FILE'] = 'При импорте файла возникли ошибки, количетсво: #count#.  Скачать лог можно по ссылке: ';
$MESS['BENDERSAY_EXPORTIMPORT_ERROR_IMPORT_FILE_FIELD'] = 'Запись #key# импортировалась, НО файл(ы) c ID "#prop#" не добавлен(ы)';
$MESS['BENDERSAY_EXPORTIMPORT_GETUSERENTITYIMPORT_ZAG'] = '<tr><th>Поля Highload-блока</th><th>Поля импортируемого файла</th></tr>';
$MESS['BENDERSAY_EXPORTIMPORT_ZAGLUSHKA'] = '<span class="required">Внимание!</span> Функционал импорта находится в разработке.'
	. ' Свяжитесь с автором для уточнения деталей <a href="mailto:anton-capi@mail.ru?subject=Вопрос по модулю Экспорт/Импорт Highload-блоков в CSV, JSON">anton-capi@mail.ru</a>';