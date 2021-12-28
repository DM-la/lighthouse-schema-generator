# Lighthouse schema generator

CONTENTS OF THIS FILE
---------------------

 * Updates
 * Description
 * Key packages
 * Installation
 * Usage
 
  UPDATES
------------

21.12.2021 - added generation graphql schemas (<b>ONLY TYPES</b>) based on <a href="https://github.com/laravel/laravel">Laravel</a> models and tables.
 
  DESCRIPTION
------------

Lighthouse schema generator is a package for generation graphql schemas made as an extension for <a href="https://github.com/nuwave/lighthouse">"nuwave/lighthouse"</a> package.

  KEY PACKAGES
------------

* Lighthouse-php (https://github.com/nuwave/lighthouse)
* Doctrine/DBAL (https://github.com/doctrine/dbal)

 INSTALLATION
------------

1. <b>Set up</b> database connection in .env configuration file.
2. Run: composer require dm-la/lighthouse-schema-generator

 USAGE
------------

Graphql schemas generation:</br>
<pre>
php artisan make:graphql-schema </br>
   -f|force - force schemas generation, rewriting existing schemas
   --models-path= - Path for models folder, relative to app path
</pre>
