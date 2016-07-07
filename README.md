ekyna/Commerce
==============

Commerce component

# Running tests

    composer install

## Behat

    $ bin/behat -c tests/behat.yml
    
## PHPUnit

    $ bin/phpunit -c tests/phpunit.xml


# TODO

- rename boolean getters from **get**XXX() ot **is**XXX().
- phpdoc property types (int/integer, bool/boolean).
- hasXXXs (at least one) method.
- setXXXs : clear current collection, use addXXX to rebuild the new collection.
- use doctrine embeddables for order addresse ?
