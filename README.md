# [Flashcard APP]

----------

# Getting started
Flashcard app made with Laravel + Artisan
## Installation

Please check the official laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/9.x/installation)



Clone the repository

    git clone https://github.com/mohdadilvp2/studocu.git

Switch to the repo folder

    cd studocu

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000


`


----------


## Environment variables

- `.env` - Environment variables can be set in this file

***Note*** : You can quickly set the database information and other variables in this file and have the application fully working.

----------
## Project Description

- To start the command line tool you can run `php artisan flashcard:interactive`. You will see the options to select menu.
