# Demo website

---

This repo aims at implementing the demo website of the Démocratie Libre project : www.democratie-libre.org

## INSTALLATION

---

### With Docker

After installing `docker` and `docker-compose`, copy `.env.dist` to `.env` and change the few parameters there. Then in `parameters.yml` you can use the environment variables like `database_host: "%env(DATABASE_HOST)%"`.

    - To fix cache and logs permissions: `make configure`
    - To install the project: `make install`
    - To launch it: `make up`

The project will be available on `http://localhost`.
An adminer is available on `http://localhost:9000` for debug purpose.

### Without Docker

Prerequisites are php and mysql installed on your local machine.

Clone the master branch of the Github repository, in a new dl directory

    git clone https://github.com/Democratie-Libre/democratie-libre.git dl

cd to the dl directory

    cd dl

Install Composer with a copy paste of the command lines in this page. This will download and launch the Composer installer

    https://getcomposer.org/download/

Then launch Composer to install the dependencies of the project

    php composer.phar install

When it is asked, enter your password for the access to mysql.

You may have to change the permissions on the directories /var/cache and /var/logs

    chmod 777 /var/cache /var/logs

Run the embedded php server

    bin/console server:run

and check that your configuration is ok using this url in your browser

    http://localhost:8000/config.php

and follow the recommandations.

Create the database and fill it

    bin/console doctrine:database:create
    bin/console doctrine:schema:update
    bin/console rad:fixture:load

The application should be ready in your browser at

    http://localhost:8000

Two users are created by default : admin (password: admin) who has the administrator rights, and user (password: user) who has the users rights.

## Code analysis

Code analysis is done thanks to [PHPStan](https://github.com/phpstan/phpstan).

To launch the tests, run `composer stan` (or `make stan` with docker).

## Specs

Specs (unit tests) are done thanks to [PHPSpec](https://github.com/phpspec/phpspec).

To launch the specs, run `composer specs` (or `make specs` with docker).

## Deploy

Using deployer to deploy.

### First, configure your ssh config

Add to your `~/.ssh/config` file :

```
Host dl-deploy
  Hostname <Address Or Ip of the Server>
  User deployment
  Port 22
  IdentityFile ~/.ssh/id_rsa
```

### Then install deployer

```
curl -LO https://deployer.org/deployer.phar
sudo mv deployer.phar /usr/local/bin/dep
sudo chmod +x /usr/local/bin/dep
```

### Forward your ssh agent

`eval $(ssh-agent)`
`ssh-add`

### Deploy

`dep deploy prod`

(Add the `-vvv` option if you want it to be more verbose)

## TODO

---

# User profile

- Following of the discussions (notifications)

# User roles

- Moderator

# General

- Proposal versions: the link to go to the previous version should not appear if it is the first version
- Put submit buttons in templates not in types
- A service to take care of the versions ? (Einenlum)
- Logic in the EditProposalType
- Translation of the messages in the FlashBag
- History of the proposal drafts
- Remove the useless methods in the repositories
- Confirmation messages for important actions (administration…)
- History of the themes ?
- PDF edition of the proposals ?
- Fork of the proposals
- Use Assetic (management of CSS and JS)
- Pagination
- Make a service to upload the files ?
- Design
- Form for the edition of the proposals : the main author should not be able to be a side author in the mean time. Form events ?
- History of the tree
- WYSIWYG editor
- Peer review ?
