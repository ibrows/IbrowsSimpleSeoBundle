IbrowsSimpleSeoBundle - Simple Seo
===================================

The IbrowsSimpleSeoBundle supports to add an for every route within additional path requirements and supports to add any metaTags to any path


Install & setup the bundle
--------------------------

1.  Fetch the source code


    ``` bash
    $ php composer.phar require ibrows/simple-seo-bundle 
    ```
	
	Composer will install the bundle to your project's `ibrows/simple-seo-bundle` directory.


2.  Add the bundle to your `AppKernel` class

    ``` php

    // app/AppKernerl.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Ibrows\SimpleSeoBundle\IbrowsSimpleSeoBundle(),
            // ...
        );
        // ...
    }
    
    ```

3.  Add routing

    ``` yaml

    // app/config/routing.yml

    SimpleSeoBundle:
        resource: .
        type:     ibrows_router
        prefix:   /

    ```

4.  Generate Schema

    ``` bash
    php app/console doctrine:schema:update  --force

    ```

Minimal configuration
---------------------

This bundle requires Nothing !


Additional configuration
------------------------

### Edit default config

	ibrows_simple_seo:
	    entity_class: Ibrows\SimpleSeoBundle\Entity\MetaTagContent
	    localized_alias: true
	    add_query_string: false
	    admin:
	        allow_create: true
	    alias:
	        maxLength: 100
	        separatorUnique: '-'
	        separator: /
	        notAllowedCharsPattern: '![^-a-z0-9_\/]+!'
	        sortOrder: {  }

    
