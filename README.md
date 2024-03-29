# PCNTL Library

Simple and easy to use thread-based process manager for PHP based on default PCNTL and POSIX functions.

![Build status](https://github.com/sweikenb/pcntl/actions/workflows/phpunit.yml/badge.svg)

**Further information:**

- [Docs](https://sweikenb.github.io/pcntl/)
- [Changelog](CHANGELOG.md)
- [MIT License](LICENSE.txt)

## Installation

```php
composer require "sweikenb/pcntl"
```

## Basic Usage

```php
use Sweikenb\Library\Pcntl\ProcessManager;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessOutputInterface;

$pm = new ProcessManager();
$pm->runProcess(function(ChildProcessInterface $child, ParentProcessInterface $parent, ProcessOutputInterface $output) {
    $output->stdout(sprintf('Hello World from PID: %d', $child->getId()));
});
```
