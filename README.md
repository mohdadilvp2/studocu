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

    php artisan migrate --env=testing
Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000


`


----------

## Docker (Sail)

To install with [Docker](https://www.docker.com) using [Sail](https://laravel.com/docs/9.x/sail), run following commands:

```
git clone https://github.com/mohdadilvp2/studocu.git
cd studocu
cp .env.example .env
composer install
php artisan sail:install
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
sail up
```
To migrate db
```
sail artisan migrate 
sail artisan migrate --env=testing
```
----------
## Testing
```
php artisan test --env=testing --coverage
```
with sail
```
sail artisan test --env=testing --coverage
```
----------
## Project Description

 The command `php artisan flashcard:interactive` should present a main menu with the following actions:
</br>
1 . Create a flashcard
</br>
The user will be prompted to give a flashcard question and the only answer to that question. The question and the answer should be stored in the database.
</br>
2 . List all flashcards
</br>
A table listing all the created flashcard questions with the correct answer.
</br>
3 . Practice
</br>
This is where a user will practice the flashcards that have been added.
First, show the current progress: The user will be presented with a table listing all questions, and their practice status for each question: Not answered, Correct, Incorrect.
As a table footer, we want to present the % of completion (all questions vs correctly answered).
Then, the user will pick the question they want to practice. We should not allow answering questions that are already correct.
Upon answering, store the answer in the DB and print correct/incorrect.
Finally, show the first step again (the current progress) and allow the user to keep practicing until they explicitly decide to stop.
</br>
4 . Stats
</br>
Display the following stats:
</br>
       - The total amount of questions.
</br>
       - % of questions that have an answer.
</br>
       - % of questions that have a correct answer.
</br>
5 . Reset
</br>
This command should erase all practice progress and allow a fresh start.
</br>
6 . Exit
</br>
This option will conclude the interactive command.
</br>
Note: The program should only exit by choosing the `Exit` option on the main menu (or killing the process)
</br>
