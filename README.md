## Weather App

## Возможности

- Просмотр текущей погоды в городах с помощью API OpenWeatherMap
- Добавление и удаление городов из своего списка с помощью React
- Экспорт данных в CSV формате
- Аутентификация пользователей и хранение их данных с помощью PostgreSQL

## Требования

- Docker
- Docker Compose

## Установка

- git clone git@github.com:EPAS05/api-php.git
- cd api-php
- make install

- После успешной установки приложение будет доступно по адресу: http://localhost:8080

## Команды Makefile

- make install - Полная установка проекта  
- make start - Запуск контейнеров
- make stop - Остановка контейнеров
- make restart - Перезагрузка контейнеров
- make logs - Просмотр логов
- make clean - Остановка и очистка контейнеров