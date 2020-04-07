# BileMo
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/075ed199499844bba18a6b66abe910bb)](https://app.codacy.com/manual/borgine/BileMoApi?utm_source=github.com&utm_medium=referral&utm_content=kirokou/BileMoApi&utm_campaign=Badge_Grade_Dashboard)
[![Maintainability](https://api.codeclimate.com/v1/badges/d6678322c967dce62065/maintainability)](https://codeclimate.com/github/kirokou/BileMoApi/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/d6678322c967dce62065/test_coverage)](https://codeclimate.com/github/kirokou/BileMoApi/test_coverage)

<p align="center">
<img src = "public/img/kirokou.png"  width="150" height="150"  title = "" alt = "kirokou">
<img src = "public/img/sf5.png"  width="150" height="150" title = "" alt = "sf5">
</p>

# How install this project ? 

## Prérequis
- Language => PHP 7.2
- Framework => Symfony
- Database => MySQL 
- Composer => 
- Web Server => 

# Install

## Download or clone the repository

    Git clone https://github.com/bpel/bilemo.git

## Download dependencies
    
    composer install

## Config .env

        // dev or prod
        APP_ENV=prod

        // define db_user & db_password
        DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/bilemo

## Config services.yaml

## Create database

    php bin/console doctrine:database:create

## Make migration

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate

## Load Fixtures

    php bin/console doctrine:fixtures:load

## Run server

    symfony server:start
    ou 
    symfony serve
    ou
    php -S 127.0.0.1:8000 -t public

