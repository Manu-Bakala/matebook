# Instructions to run the web application correctly

## Database
Go to the root of the project with the terminal (matebook-main)
Run these commands to create the structure of the database
***************
symfony console:doctrine:create
symfony console:doctrine:schema:create
***************

## Installation
Run these commands in the terminal to install dependencies into vendor and to add encore package
***************
symfony composer install
npm add --dev @symfony/webpack-encore
npm add webpack-notifier --dev
npm run dev
***************
(if you use the Yarn package manager, replace "npm" with "yarn")

## Starting the web application
Run these commands in the terminal to start the server and to open the web application
***************
symfony console server:start
symfony open/local
***************

## MailTrap (Receipt of emails)
Email receipt is managed with MailTrap.
Go to "https://mailtrap.io" and log in with the email and the password below
- email : matebook.belgium@gmail.com
- password : Matebook-bak-helb

## GitHub link
https://github.com/Manu-Bakala/matebook