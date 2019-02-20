# EVE Citadel

Система централизованной авторизации на альянсовых игровых сервисах EVE Online.

## Futures
#### Основные возможности:
* Авторизация в системе через EVE SSO
* Авторизация в поддерживаемых сервисах
* Периодичная проверка пользователей

#### Поддерживаемые сервисы:
* Discord
* TeamSpeak3
* phpBB3

## History

В процессе слияния двух альянсов в EVE Online, потребовалась более простая альтернатива проекту [Alliance Auth](https://github.com/allianceauth/allianceauth).
На тот момент у одного альянса был лишь сервер дискорд и бот [Broadsword](https://github.com/hiveliberty/Broadsword) для авторизации игроков альянса на сервере дискорда, а второй альянс использовал, ранее упомянутый, Alliance Auth.

В итоге, был написан веб-сервис на микро веб-фрэймворке Slim для PHP.

## Getting Started

Инструкции ниже помогут установить систему.

### Prerequisites

Эта система тестировалась на Debian 9, но проблем с другими быть не должно. 
Я не буду описывать, как установить некоторый необходимый софт\зависимости - есть масса примеров в интернете.

```
Необходимые компоненты:
apache2
libapache2-mpm-itk
php5.5+
php-pear
php-mbstring
php-xml
unzip
mariadb
composer
```

### Installing
```
Клонируем репозиторий:
#git clone https://github.com/hiveliberty/eve-citadel.git
#cd eve-citadel

Актуализируем (обновление выполнять аналогичным образом):
#git checkout v1.X.X (1.X.X - смотрите последнюю актуальную версию)

```
to be continue...

## Built With

* [Slim 3.1](https://www.slimframework.com/docs/) - micro web-framework for PHP
* [Composer](https://getcomposer.org/) - PHP Dependency Management
* [restcord](https://github.com/restcord/restcord) - Discord REST API Client
* [oauth2-discord](https://github.com/moutard3/oauth2-discord) - Discord OAuth2 client

## Versioning

Проект использует [SemVer](http://semver.org/) для версионирования. Но это не точно :)<br>
Чтобы просмотреть доступные версии, смотрите [тэги этого репозитория](https://github.com/your/project/tags).

## Authors

* **Eino Efimov** - *Initial work* - [hiveliberty](https://github.com/hiveliberty)

Полный список участников - [contributors](https://github.com/hiveliberty/eve-citadel/contributors).

## License

Этот продукт распространяется под лицензией [MIT License](LICENSE.md).

## Acknowledgments

* Этот проект использует библиотеку [ts3admin](http://ts3admin.info). Библиотека встроена.

