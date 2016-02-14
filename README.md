# Demo website 
----

This repo aims at implementing the demo website of the Démocratie Libre project : www.democratie-libre.org

## INSTALLATION
----

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

## TODO
----

# User profile

* Following of the discussions (notifications)

# User roles

* Moderator

# General

* Put submit buttons in templates not in types
* A service to take care of the versions ? (Einenlum)
* Logic in the EditProposalType
* Translation of the messages in the FlashBag
* History of the proposal drafts
* Remove the useless methods in the repositories
* Confirmation messages for important actions (administration…)
* History of the themes ?
* PDF edition of the proposals ?
* Fork of the proposals
* Use Assetic (management of CSS and JS)
* Pagination
* Make a service to upload the files ?
* Design
* Form for the edition of the proposals : the main author should not be able to be a side author in the mean time. Form events ?
* History of the tree
* WYSIWYG editor
* Peer review ?
