Tournia.net site
========================

These are the files that are hosted on [Tournia.net](https://www.tournia.net/).
Feel free to use it, but read the [LICENSE](LICENSE) file when using the code for other purposes than personal use.
Also, if you find issues, please open an issue in this repository, and if you already know how to fix it, create a pull request.

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

TL;DR
----------------------------------
1. Install [Vagrant](http://www.vagrantup.com)
2. Enter in the terminal: `vagrant up`
3. Go to http://192.168.50.4/app_dev.php to check if Tournia is working. You can login with username: *tournia* and password: *pocahontas*
3. You can login the system with the username: *tournia* and password: *pocahontas*


Introduction
----------------------------------
The Tournia system uses [Symfony](http://symfony.com/).
There is a lot of official documentation for Symfony available. It is highly recommended to at least go through the [quick tour](http://symfony.com/doc/current/quick_tour/the_big_picture.html). This explain the basics of Symfony; it's an advanced framework and might seem complicated, but we use a great deal of functionality out of it, and makes development easier and better.
If you're having some time left, you can move onto reading the [official Symfony2 book](http://symfony.com/doc/current/index.html).

### Vagrant installation
The repository files contain a Vagrant file, which can be used to setup a MAMP/WAMP environment quickly. [Vagrant](https://www.vagrantup.com/) is free software which creates a virtual OS, on which the Apache, MySQL and PHP server is installed. Just follow the simple steps:

1. [Download](http://www.vagrantup.com/downloads.html) and install Vagrant.
2. Vagrant uses virtualization software, for example VMWare Fusion, Parallels, or the free [VirtualBox](https://www.virtualbox.org/). Download and install one of these (VirtualBox is probably fine).
3. Go in your terminal to the repository files, and type: `vagrant up` which will start your virtual machine. 
4. You can connect to your virtual server by typing `vagrant ssh`
5. Go to [http://192.168.50.4/app_dev.php](http://192.168.50.4/app_dev.php) to check if your virtual server is working. You can login with the username: *tournia* and password: *pocahontas*
6. You can login the system with the username: *tournia* and password: *pocahontas*
7. Happy coding! :)

#### Vagrant tips
Some tips for when you've got things working:

- Every time you want to start your virtual Vagrant OS, just type: `vagrant up`. You can open the virtual OS with `vagrant ssh`. For other commands, see [https://docs.vagrantup.com/v2/cli/index.html](https://docs.vagrantup.com/v2/cli/index.html)
- You can change the Vagrant settings in the Vagrantfile in the repository (check the [docs](https://docs.vagrantup.com/v2/vagrantfile/index.html)), or the Vagrant_bootstrap.sh file for specific provisioning configuration ([docs](https://docs.vagrantup.com/v2/provisioning/index.html)). The Vagranfile also contains the default IP-adress, so you can change this if you want to.
- You can make changes to files on your local hard-drive, which are synced with the virtual Vagrant machine (via the NFS protocol). However, it might take 1-2 seconds to synchronize, so if you don't immediately see your changes, wait a few seconds, and reload the page in your browser.
- To update the Vagrant virtual machine, you can run the command `vagrant reload --provision` This runs the bash script (Vagrant_bootstrap.sh) again. You can also destroy the virtual machine, and re-create it by: `vagrant destroy` and `vagrant up`

## Docker
There is a Docker compose configuration available, although this needs to be tweaked. Run
```bash
$ docker-compose build
$ docker-compose up
```
You can stop the container with Ctrl-C. Tournia is available on http://localhost/app_dev.php

To clean up the docker images, run `docker-compose rm`. To execute a command in the container, run `docker-compose exec web bash`.


### Make Symfony work
By now you should be able to see http://192.168.50.4/app.php/ and have a fully working website. 
The data is used from the file testDump.sql, which is imported in the MySQL database. You can check this at http://192.168.50.4/phpmyadmin/ in the *tournament* table.

### Assetic
You are normally working in the development environment (url contains app_dev.php). This way, assetic re-creates CSS and JavaScript files automatically when changes have been made. You can also manually run Assetic. To do this, run the command:

    php app/console assetic:dump --env=prod --no-debug
This command can also be useful when changing SASS files, because Assetic might not re-generate the corresponding CSS files. In that case, you can also delete the cache files in app/cache/dev/*


It is advisable to create a symbolic link (shortcut), with the command

    php app/console assets:install public_html --symlink
This is useful because if you change something in src/TS/*Bundle/Resources/public, it will automatically be changed in public_html/bundles/

### Development tips
When changing from one branch to the other, you might run into some problems.

- The database scheme might be different. When there have been made changes to entity files, the system will display an error when using this entity. The entity is in that case not the same as your database. To check your database with the entities, run the command: `php app/console doctrine:migrations:migrate (-n)`
- The javascript, css and image files in public_html/bundles or old. Update these with the command: `php app/console assets:install public_html (--symlink)`
- Cached files might not be up-to-date anymore. Simply delete app/cache/*

Background information
----------------------------------

### Bundels
Besides using the bundles from the Symfony (and other vendors) software, we have also our own bundles:

- FrontBundle: This is the index page of www.tournia.net. It contains the index page, contact page, tournament overview, logging in, changing password and account information. In the future it will be expended to e.g. show feature and pricing information.
- SiteBundle: the tournament website, with pages of tournament information, players, groups, Live, registration and payment for a tournament. It has the url app[_dev].php/tournamentUrl/*
- ControlBundle: controlling a tournament for organizers, i.e. showing the registrations, some statistics, creating teams, matches, showing rankings, announcements, etc. It has the url app[_dev].php/tournamentUrl/control/*
- ApiBundle: all the logic for managing teams, matches, rankings, announcements, etc. The difference with ControlBundle is that ControlBundle has the visual elements, and ApiBundle works with a Json or XML files. It is used by ControlBundle and Live of SiteBundle, but can (in the future) also be used by external websites. See the [API documentation](https://www.tournia.net/en/developers) 
- FinancialBundle: all financial stuff, for example buying a product for a tournament, and paying with iDeal or PayPal.
- LiveBundle: similar to the Live functionality of SiteBundle, but mobile-friendly which can be used for players during a tournament.
- SettingsBundle: all settings of a tournament, only accessible by tournament organizers. This is separate of the ControlBundle, because the settings are changed before the tournament, and control is used during the tournament.

### Entities
Our system has a lot of entities. Every entity is a file in the src/TS/*Bundle/Entity/* folder and corresponds to a table in the database. The entities are constantly changing, but below is a general overview of the structure of the system.

SiteBundle related:

- **User**: someone that can log in to the website and get access rights.
- **AuthorizationInvite**: to grant a user access to a tournament (as an organizer) or player (as the controlling user), an invite with a secret code is emailed. The user can click the link in the email to activate the access right.
- **Tournament**: the main entity which is used to save the name, url (e.g. utrecht2013, used for www.tournia.net/isbt-london-2016/), players, users that are organizer and some other settings
- **Site**: a tournament has an own (web)site. A site can have files, pages, an address and some settings about payment and Live
- **SitePage**: a page on a Site. A page has html content, title and url (e.g. index.html)
- **File**: an uploaded file, which has a name, size and reference to the location of the file. Files can have a specialType, for e.g. standard header or background images that can be used by all tournaments.
- **Payment**: a player can pay for the tournament via PayPal, but an organizer can also add a payment. A payment has an amount, date, relation to a player and executing user. The method can be PayPal, bank transfer or other.
- **Category**: when a player registers for the tournament, this has to be done in categories (e.g. Men Singles A or Easy or Doubles). A category has a name, which genders can register (men, ladies, both) and maximum number of players in a team. It references the teams, players that are in the category but don't have a team yet and matches.
- **RegistrationFormField**: the form for registering a player for a tournament can be expanded with more fields, e.g. textarea, radio buttons, etc. A RegistrationFormField can have a help tooltip text, name, whether it's required to fill in the form field and choice options (for radio buttons and selections).
- **RegistrationFormValue**: relationship between RegistrationFormField and Player, with a filled in value.
- **Player**: the user registers a player for a tournament. One user can also register multiple players. A player has a first name, family name, gender, registration date, status (defined in tournament), relation to user (can be empty), whether it's the teamcaptain (contact person of the RegistrationGroup), reference to other RegistrationFormValues, whether the player is ready to play and some other relationships.

ControlBundle related:

- **Location**: a tournament has locations where matches are played. A location has a name, possibly a match and whether the location is on hold (i.e. ready).
- **Team**: for doubles and mixed the two players form a team. For technical reasons also with singles, the only player is a team. The team has a name (default combination of player names), reference to the category, players that are in the team and their position (i.e. which player is visually displayed left and which displayed right for doubles/mixed). A team can be given up and has matches; there are two fields for matches, whether it's the team visually displayed on the left side or on the right.
- **Match**: a match is between two teams, in a category, on a location (although not necessary), between teams, has a start time, a round, can be given priority, has a status (ready, played, etc.) and has a score.
- **Announcement**: when a match is created, this can be announced. Announcements can also be for 2nd calls or other things that should be announced. An announcement has a type, match, players it relates to and a time.
- **UpdateMessage**: to be able to notify clients of changes made, and to have a log of all the changes, all changes are saved as a message. It has a type, title, text, time, user that executed it and section it relates to (to be able only to update the parts on the client that relate to it).

Development tips
----------------------------------

### Migrations
To create a new migration: `php app/console doctrine:migrations:generate` (or possibly `php app/console doctrine:migrations:diff`) and `php app/console doctrine:migrations:migrate`

### Websocket
Run `php app/console gos:websocket:server --port 8080` to start accepting websocket connections for Match Control.
