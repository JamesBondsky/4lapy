# 4 лапы

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
- ./bin/symfony_console f:s:s # на сегодняший день
- ./bin/symfony_console f:s:s --date="$(date --date="tomorrow" +%Y-%m-%d)" # на завтра
```

## Смена типа оплаты для неоплаченных заказов с оплатой онлайн

```
- ./bin/symfony_console f:o:p:c
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
