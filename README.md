Symfony-doctrine-graphql-api
=====

# Description
This library provides an single and simple entry point to read all your db using Graphql syntax.
To achieve that we will need to export our db model into php entities, which will be represented
  by doctrine annotations, taking advantage of this doctrine annotations this library will generate
  the graphql schema to accessed using Graphql query syntax.

# Setup

## Include repository in composer.json

```
"repositories": [
....
    {
        "type": "git",
        "url": "http://git.trivago.trv/pse/bootstrapbundle.git"
    },
....
```

## Installing dependencies
```
composer install
```

Set up doctrine database connection This library will only require an valid doctrine connection as the one above:
```
doctrine:
    dbal:
        driver:   "%database_driver%"
        host:     "%database_host%"
        port:     "%database_port%"
        dbname:   "%database_name%"
        user:     "%database_user%"
        password: "%database_password%"
        charset:  UTF8

    orm:
        default_entity_manager: default
        auto_generate_proxy_classes: "%kernel.debug%"
        naming_strategy: doctrine.orm.naming_strategy.underscore
        auto_mapping: true
```

Including graphql entry point in 'app/config/routing.yml'
```
# Adding graphql routes (127.0.0.1:8000/graphql/api?query={graphql_query}
graphql_routing:
    resource: .
    type: extra
```

## Importing your DB Model

As it was mentioned above, your db model will be imported from connection defined under Doctrine.dbal.default.

For the following export command we had use `"AppBundle\\Entity"` as namespace for model to be imported, and `./src/AppBundle/Entity` 
as location folder to place each of the entity files.

```
php app/console graphql-api:graphql:generate-schema --namespace "AppBundle\\Entity" annotation ./src/AppBundle/Entity --from-database --force
```



##Usage

Taking in account you have the server running under `127.0.0.1:8000`...

#### Curl sample
```
curl -XPOST -H 'Content-Type:application/graphql' -d 'query={  boardtype(id: 2) {  id, name, vatClass { amount }  } }' http://127.0.0.1:8000/graphql/api
```

#### Browser sample
```
http://127.0.0.1:8000/graphql/api?query={boardtype(id:2){id,name,vatClass{amount}}}
```

If you want to know more about Graphql syntax, go to: http://graphql.org/learn/

## Troubleshooting

### Using Postgres blob type
In case your db is Postgres and includes `Blob` types you might need to patch the 
postgres driver due to `Blob` wasn't included in latest version of driver.

####Patching Postgres driver to include new types
```
patch vendor/doctrine/dbal/lib/Doctrine/DBAL/Driver/AbstractPostgreSQLDriver.php patch/AbstractPostgreSQLDriver.patch
```
