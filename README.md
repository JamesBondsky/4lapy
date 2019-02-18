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
- ./bin/symfony_console r:c manzana_update # обработка очереди передачи контактов в ML
- ./bin/symfony_console r:c catalog_sync   # обработка очереди изменения элементов каталога для изменения индекса elastic 
- ./bin/symfony_console r:c callback_set   # обработка очереди отправки сообщений о запросе обратного звонка на АТС
- ./bin/symfony_console r:c manzana_referral_add   # обработка очереди передачи рефералов в ML
```

## Перезапуск консьюмеров манзаны по расписанию
```
- /usr/bin/supervisorctl restart 4lapy_manzana_update
- /usr/bin/supervisorctl restart 4lapy_stage_manzana_update
```

## Запуск импорта из SAP 

```
- ./bin/symfony_console f:s:i catalog #Каталог (товары -> цены (+ простые акции) -> остатки на складах -> остатки в магазинах)
- ./bin/symfony_console f:s:i order_status #Статусы заказа (заказы из SAP)
- ./bin/symfony_console f:s:i payment # Задания на списание оплаты
- ./bin/symfony_console f:s:i delivery_schedule # Расписания поставок
- ./bin/symfony_console f:s:i bonus_buy # Сложные скидки из SAPBB 
```
```
-f|--force - для сброса блокировки
```

## Запуск пересчета графиков доставок

```
- ./bin/symfony_console f:s:s # на завтрашний день
- ./bin/symfony_console f:s:s --date="2000.01.01" # на конкретную дату
```

## Запуск импорта местоположений DPD

```
- ./bin/symfony_console f:d:d:i
```


## Смена типа оплаты для неоплаченных заказов с оплатой онлайн

```
- ./bin/symfony_console f:o:p:c
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

## Фабрика фидов

```
- ./bin/symfony_console b:f:f %id% --type %type% # id - ид профиля выгрузки, type - тип фида (yandex-market; google-merchant; retail-rocket)
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
- ./bin/symfony_console f:i:r 
- ./bin/symfony_console f:i:r -f # С пересозданием индекса 
```

## Сбросить пароль для пользователей группы FRONT_OFFICE_USERS
```
- ./bin/symfony_console f:f:p:r
```

## Запуск тестов

При первом запуске выполнить: 
```
# Необходим поисковый индекс для тестового окружения
- ./bin/symfony_console --env=test f:i:r

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
