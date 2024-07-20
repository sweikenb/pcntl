# Installation

Install the latest version using [composer](https://getcomposer.org/):

```bash
composer require sweikenb/pcntl
```

## Changelog

Please consult the [CHANGELOG.md](https://github.com/sweikenb/pcntl/blob/main/CHANGELOG.md) for latest update
information.

## System Requirements

This library requires at least **PHP v8.2** with the following extensions enabled:

- `pcntl`
- `posix`
- `sockets`

Note that this library is **incompatible** with the `grpc` extension!

In order to execute the unit-tests you will need to run them in a linux environment.
