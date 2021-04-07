<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Тестовое задание на Yii2</h1>
    <br>
</p>

### Установка:

Клонировать репозиторий:
~~~
git clone https://github.com/pogorivan/test-task
~~~

Установить зависимости:

~~~
composer install
~~~

Создать базу данных Mysql, настроить подключение к базе данных в файле config/db.php
Запустить миграции:

~~~
./yii migrate
~~~

### Использование:

Для запуска импорта товаров из файла используестся консольная команда:
~~~
./yii import
~~~

Api для работы с таблицей товаров располагается по адресу http://test-task/product-api, метод для загрузки пачкой до 50 товаров - http://test-task/product-api/batch-create, принимает JSON массив следующего вида:
~~~
{
  "products": [
    {
      "code": "34324",
      "name": "Товар 1",
      "price": "23.4"
    },
    {
      "code": "23423-fd",
      "name": "Товар 2",
      "price": "46"
    },
    {
      "code": "hj-4324",
      "name": "Товар 3",
      "price": "56.333"
    }
  ]
}
~~~