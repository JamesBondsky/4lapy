# 4 лапы

## Отключение почты:

```
Для того, чтобы отключить отправку почты необходимо изменить адрес в который стучится API.
Файл: /app/config/parameters.yml
expertsender:
    url: 'https://api.esv2.com'
    key: ivaacmVjzakOzRLeYjKy
    
https://api.esv2.com - валидный адрес    
```

## Обновление коробки Vagrant:

```
- vagrant halt
- vagrant destroy
- vagrant box update
- vagrant box prune
- rm -rf common/bitrix
- vagrant up
- vagrant ssh
- cd ~/project
- composer install
```
## Запуск консьюмеров для rabbit'а

```
- ./bin/symfony_console rabbitmq:consumer expert_sender_send_pets # отправка сообщений с информацией о питомцах в ES
- ./bin/symfony_console rabbitmq:consumer manzana_update # обработка очереди передачи контактов в ML
- ./bin/symfony_console rabbitmq:consumer catalog_sync   # обработка очереди изменения элементов каталога для изменения индекса elastic 
- ./bin/symfony_console rabbitmq:consumer callback_set   # обработка очереди отправки сообщений о запросе обратного звонка на АТС
- ./bin/symfony_console rabbitmq:consumer manzana_referral_add   # обработка очереди передачи рефералов в ML
- ./bin/symfony_console rabbitmq:consumer manzana_orders_import # обработка очереди запроса заказов пользователей в ML
- ./bin/symfony_console rabbitmq:consumer import_offers # обработка очереди импорта промокодов
- ./bin/symfony_console rabbitmq:consumer manzana_mobile_update # обработка очереди обновления параметров пользователя в манзане
- ./bin/symfony_console rabbitmq:consumer push_processing #обработка обычных пушей
- ./bin/symfony_console rabbitmq:consumer push_file_processing #обработка пушей из файла
- ./bin/symfony_console rabbitmq:consumer push_send_ios #отправка ios пушей
- ./bin/symfony_console rabbitmq:consumer order_subscription_creating # срочное создание заказов по отдельным подпискам 
```

## Перезапуск консьюмеров манзаны по расписанию
```
- /usr/bin/supervisorctl restart 4lapy_manzana_update
- /usr/bin/supervisorctl restart 4lapy_stage_manzana_update
- /usr/bin/supervisorctl restart 4lapy_stage_manzana_import
- /usr/bin/supervisorctl restart 4lapy_manzana_orders_import
```

## Запуск импорта из SAP 

```
- ./bin/symfony_console fourpaws:sap:import catalog #Каталог (товары -> цены (+ простые акции) -> остатки на складах -> остатки в магазинах)
- ./bin/symfony_console fourpaws:sap:import order_status #Статусы заказа (заказы из SAP)
- ./bin/symfony_console fourpaws:sap:import payment # Задания на списание оплаты
- ./bin/symfony_console fourpaws:sap:import delivery_schedule # Расписания поставок
- ./bin/symfony_console fourpaws:sap:import bonus_buy # Сложные скидки из SAPBB 
```
```
-f|--force - для сброса блокировки
```

## Запуск пересчета графиков доставок

```
- ./bin/symfony_console fourpaws:store:schedulescalculate # на завтрашний день
- ./bin/symfony_console fourpaws:store:schedulescalculate --date="2000-01-01" # на конкретную дату (сгенерируется на следующий день после указанного)
```

## Генерирование заказов по подписке

```
- ./bin/console fourpaws:orderssubscribe:send Обход подписок и генерация заказов
```

## Запуск импорта местоположений DPD

```
- ./bin/symfony_console f:d:d:i
```


## Смена типа оплаты для неоплаченных заказов с оплатой онлайн

```
- ./bin/symfony_console fourpaws:order:paysystem:change
```

## Получение из Manzana заказов для пользователей, активных за последнее время

```
- ./bin/symfony_console fourpaws:sale:order:manzana:import # за 1 месяц
- ./bin/symfony_console fourpaws:sale:order:manzana:import --period="2 month" --mq=1 # period - за произвольный период; user - ID пользователя, для которого выгрузить (период при этом не учитывается); mq - использовать сервер очередей для импорта
```

## Деактивация завершившихся акций

```
- ./bin/symfony_console f:s:a:a:c
```

## Отправка сообщений по забытым корзинам

```
- ./bin/symfony_console f:s:f:s 1 # уведомление о забытой корзине
- ./bin/symfony_console f:s:f:s 2 # повторное уведомление
```

## Рассылка push-сообщений

```
- ./bin/symfony_console bitrix:mobileApi:push:queue
```

## Рассылка персональных предложений на почту (не используется, работает некорректно)

```
- ./bin/symfony_console fourpaws:popup:notification                  # рассылает уведомления по персональным предложениям, которые закончатся через 4 дня
- ./bin/symfony_console fourpaws:popup:notification -t start         # рассылает уведомления по персональным предложениям, которые начинаются в текущий день
```

## Фабрика фидов

```
- ./bin/symfony_console bitrix:feed:factory %id% --type %type% # id - ид профиля выгрузки, type - тип фида (yandex-market; google-merchant; retail-rocket; expert-sender)
```

## Сервисы вагранта

* [MailHog](http://4lapy.vag:8025/)
* [Rabbit](http://4lapy.vag:15672/)
```
login: guest
password: guest
```
* [Kibana](http://4lapy.vag:5601/)


## Запуск переиндексации
```
- ./bin/symfony_console fourpaws:indexer:reindex 
- ./bin/symfony_console fourpaws:indexer:reindex -f # С пересозданием индекса 
```

## Сбросить пароль для пользователей группы FRONT_OFFICE_USERS
```
- ./bin/symfony_console f:f:p:r
```

## Запуск тестов

При первом запуске выполнить: 
```
# Необходим поисковый индекс для тестового окружения
- ./bin/symfony_console --env=test fourpaws:indexer:reindex

# Билдим исходники для codeception 
- .php vendor/bin/codecept build 
```

Запуск тестов:
```
# Запускаем все тесты
- .php vendor/bin/codecept run 

# Запускаем тесты с большим количеством логов
- .php vendor/bin/codecept run -vvv

# Запускаем тесты из конкретного файла
- .php vendor/bin/codecept run tests/api/rest/CardCest.php
```
