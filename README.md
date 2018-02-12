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
- ./bin/symfony_console r:c manzana_update # Manzana
- ./bin/symfony_console r:c catalog_sync   # Синхронизация каталога 
- ./bin/symfony_console r:c callback_set   # Колбэк
```
