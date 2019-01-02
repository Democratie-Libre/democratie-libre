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

Get into the directory 'refonte-dl' that you have just download (a composer.json file should also be present, we will stay there in the following)

You have to download the dependencies. For that download firstly composer with the following command :

    curl -s http://getcomposer.org/installer | php

Then do :

    php composer.phar install

This last command will install all the dependencies and will ask a few configuration parameters for your database in particular (you can keep the default values but not for the name and the password if you have one, you could face a dependency problem if you have not already installed 'php intl')

For those who work with Linux, you have to give the writing rights for the files 'app/cache' and '/app/logs'. For that use following commands :

    sudo setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs
    sudo setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

Check your PHP configuration by using the following URL in your browser :

    http://votre/chemin/projetdL/web/config.php

In principle a PHP version 5.4.11 or above is needed.
If you are asked to update the 'timezone' in '/etc/php5/apache2/php.ini, in principle you also have to update it in '/etc/php5/cli/php.ini
If after refreshing the page you get the message "Your configuration looks good to run Symfony" you can proceed further

Use the bin/install script that contains everything to install the project :

```bash
app/console doctrine:database:drop --force " Destroy the database (beware of your parameters.yml!)
app/console doctrine:database:create " Create the database
app/console doctrine:schema:create " Create the schema
app/console rad:fixtures:load " Load the fixtures
```

Then run a simple php embedded server with:
`app/console server:run`

Then the application should then be ready from your browser at the address :

`http://localhost:8000`

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

curl -LO https://deployer.org/deployer.phar
sudo mv deployer.phar /usr/local/bin/dep
sudo chmod +x /usr/local/bin/dep

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
