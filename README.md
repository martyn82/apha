[PHP 7+]: (http://php.net)
[Composer]: (https://getcomposer.net)
[MongoDB]: (https://www.mongodb.org)
[ElasticSearch]: (https://www.elastic.co/products/elasticsearch)

Apha
====

Apha is a CQRS/ES library for PHP. It contains all the building blocks
you need to build an application that implements Command-Query Responsibility
Segregation either with or without Event Sourcing.

Apha provides:
* Typed command and event handling
* Using MongoDB as event store and/or read store
* Using ElasticSearch as read store
* Replay of events
* Sagas (experimental)
* Event scheduling (experimental)

The library is fully unit tested.

## Prerequisites

Requirements:
* [PHP 7+]
* [Composer]

Optional:
* [MongoDB]
* [ElasticSearch]

## Installation

```
$ composer install
```

## Running

Run the tests:
```
$ bin/phing test:unit
```

## Documentation

Currently, there is no general documentation available. Coming soon!

## Examples

Some quickstart examples are available in the `docs/examples` directory.
You can run them from the command-line by executing:

```
$ php docs/examples/ ...
```

## Licensing
Please consult the file named `LICENSE` in the root of the project.
