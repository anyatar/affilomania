# CRUD project

## Requirements

1. PHP 7.0 or later
2. Apache Web Server with mod_php
3. Linux (other OSs can work but require small changes to the permissions instructions)

## Installation

1. Clone the project from git to a location on your server.  This location should be outside of the Web server's doc root.
2. Under the checked out folder, change permissions:
   chmod 777 data/cache
3. Create a new database:
   mysql -u username -p affilomania
4. Configure the database connection in config/autoload/global.php
5. Import the default database:
   mysql -u username -p affilomania < affilomania.sql
6. Add a new vhost to Apache:
```
	<VirtualHost *:20080>
	DocumentRoot "/path/to/project/public"
	<Directory "/path/to/project/public">
	   Options Indexes FollowSymLinks ExecCGI
	   AllowOverride All
	   Require all granted
	  </Directory>
	</VirtualHost>
```
   It's also possible to change the existing default vhost to these settings.

7. Restart Apache
8. Open http://server_address:20080/
9. Enjoy!
