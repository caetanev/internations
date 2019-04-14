# internations
Internations Challenge API

How to Install this Project:

    1) git clone https://github.com/caetanev/internations.git
    2) composer install
    3) php bin/console doctrine:database:create
    4) php bin/console doctirne:fixture:load
    5) php bin/console server:start *:8081
    6) The project is now accessible through http://localhost:8081

How to perform the PHPUnit test:

    php bin/phpunit

Documentation for the API

    http://localhost:8081/api/doc

The API uses JWT as authentication method. To log in as Administrator you must follow:

   Perform a login with the Administrator credentials to retrieve the token:
   
    $ curl -X POST -H "Content-Type: application/json" http://localhost:8081/auth/check -d '{"username":"administrator","password":"secret123"}'

   Once you get the token, any call to the API has to have the header Authorization: Bearer:
   
    $ curl -H "Authorization: Bearer [TOKEN]" http://localhost:8081/api/users 
    
   To make life easier I suggest to use Postman to make all the API calls.
   
The required artifacts Domain Model and Database Model are in the root of the project

    domainModel.pdf
    databaseModel.pdf



 
