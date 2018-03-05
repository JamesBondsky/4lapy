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

### Сервисы вагранта

* [MailHog](http://4lapy.vag:8025/)
* [Rabbit](http://4lapy.vag:15672/)
```
login: guest
password: guest
```
* [Kibana](http://4lapy.vag:5601/)
