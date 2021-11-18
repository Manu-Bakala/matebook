# MateBook - Instructions to run the web application correctly

## Database
Go to the root of the project with the terminal (matebook-main)

Run these commands to create the structure of the database:

- symfony console:doctrine:create
- symfony console:doctrine:schema:create
***************

## Installation
Run these commands in the terminal to install dependencies into vendor and to add encore package:

- symfony composer install
- npm add --dev @symfony/webpack-encore
- npm add webpack-notifier --dev
- npm run dev

(if you use the Yarn package manager, replace "npm" with "yarn")
***************

## Starting the web application
Run these commands in the terminal to start the server and to open the web application:

- symfony console server:start
- symfony open/local
***************

## MailTrap (Receipt of emails)
Email receipt is managed with MailTrap.
- Go to "https://mailtrap.io" and sign up for free.
- Add a Project
- Add an Inbox named Matebook
- Go to the inbox created and go to the SMTP Settings
- Copy the user code in apostrophes
- Go to the source code of the project and open the file named 'env.local'
- replace this line
  - MAILER_DSN=smtp://67bfc38efefb33:c19cfb40dcd6e7@smtp.mailtrap.io:2525

- By this line
  - MAILER_DSN=smtp://'your user code'@smtp.mailtrap.io:2525

All emails from the web application will be handled by this inbox
***************

## GitHub link
https://github.com/Manu-Bakala/matebook
