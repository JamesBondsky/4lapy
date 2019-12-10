# 4 лапы

## Установить зависимость проекта

```
composer install 
```

## Развернуть статику

```
в папке common сделать папку static
https://gitea.articul.ru/4Lapy/4lp-static
```
## Симлинки

```
common/bitrix => vendor/4Lapy/bitrix
web/bitrix => common/bitrix
web/local => common/local
web/static => common/static
```

## Права на папку var

```
chmod -R 777 var
```

## Файлы с настройками

```
в папку vendor/4Lapy/bitrix/php_interface положить файл local.env.php
в папку app/config положить файл parameters.yml

Файлы взять с ddev
```

## Собрать образ и запустить проект

```
в корне запустить 
docker-compose build (длительная процедура)
docker-compose up -d
```

## Индексация эластики

```
Для работы каталога запустить
docker-compose exec php ./bin/symfony_console fourpaws:indexer:reindex -f --batch 20
```
