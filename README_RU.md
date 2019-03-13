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

## Getting Started

Инструкции ниже помогут установить систему.

### Prerequisites

Эта система тестировалась на Debian 9, но проблем с другими быть не должно. 
Я не буду описывать, как установить некоторый необходимый софт\зависимости - есть масса примеров в интернете.

```
Необходимые компоненты:
apache2
php5.5+
php-pear
php-mbstring
php-xml
unzip
mariadb
composer
```

```
Не обязательные компоненты (по желанию):
libapache2-mpm-itk (не обязательное)
```

Вам необходимо создать приложение на портале разработчиком EVE-Online:
https://developers.eveonline.com/<br>
Для использования с Discord необходимо будет создать бота тут:
https://discordapp.com/developers/applications/

### Installing
Установка будет описана под ОС Debian 9.

```
Клонируем репозиторий, например, в /var/www:
#git clone https://github.com/hiveliberty/eve-citadel.git
У вас должна появиться директория eve-citadel.

Cоздаем базу (субд у вас уже должна быть установлена).
Создаем поьзователя с полными правами на эту базу.
Импортируем дамп базы (db_init.sql) из папки install.

Выполняем конфигурацию приложения в директории config.
Файлы *.template.php переименовываем в *.php (* - имя файла)

Добавляем владельца-альянс и выполняем создание и синхронизацию групп:
#cd /var/www/eve-citadel
#php manager.php owner add your_alliance_id
#php manager.php groups init
#php cronjobs/update_db.php

Из корня папки необходимо скопировать файл evecitadel в /etc/cron.d, поправив пути до файлов.

Это необходимый минимум, который необходимо выполнить.

За более подробной помощью можно обращаться через Discord.

to be continue...
```

## Built With

* [Slim 3.1](https://www.slimframework.com/docs/) - micro web-framework for PHP
* [Composer](https://getcomposer.org/) - PHP Dependency Management
* [restcord](https://github.com/restcord/restcord) - Discord REST API Client
* [oauth2-discord](https://github.com/moutard3/oauth2-discord) - Discord OAuth2 client

## License

Этот продукт распространяется под лицензией [MIT License](LICENSE.md).

## Acknowledgments

* Этот проект использует библиотеку [ts3admin](http://ts3admin.info). Библиотека встроена.
