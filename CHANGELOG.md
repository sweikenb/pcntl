# Changelog

## Release [v7.1.0](https://github.com/sweikenb/pcntl/releases/tag/v7.1.0)

**Features**

- Introduced the `unblock()`-function to the `ProcessManager` so the process-unblock and signal dispatching can be
  triggered externally too

* * *

## Release [v7.0.0](https://github.com/sweikenb/pcntl/releases/tag/v7.0.0)

**Bugfixes**

- Proper signal handling and propagation #11

**Features**

- Introduced a `wait()`-function for the `ProcessQueue` itself which should be used when working with queues instead
  of using the `wait()` method of the `ProcessManager` itself.
- Adding PHP 8.3 support to phpunit test-matrix

**Breaking Changes**

- NOTE: The POSIX signal handling fix might affect the order in which callbacks will be called.
  For the most part, this should not change the functionality of your application, but just to make sure nothing breaks
  unexpectedly, this is the reason for the major version bump instead of just a feature-release.

* * *

## Release [v6.0.0](https://github.com/sweikenb/pcntl/releases/tag/v6.0.0)

**Bugfixes**

- Sending IPC messages will now honor the returned bytes of the written buffer correctly

**Features**

- `ProcessOutput` allows to modify the console output beside the default `STDOUT` and `STDERR`
- Unit and functional tests added using PHPUnit and GitHub actions
- Dedicated documentation added with static rendering using [mdBook](https://rust-lang.github.io/mdBook/) and GitHub
  actions

**Breaking Changes**

- `ProcessPool` has been removed in favor of the more simplistic `ProcessQueue` approach

* * *

## Release [v5.0.0](https://github.com/sweikenb/pcntl/releases/tag/v5.0.0)

**Features**

- `ProcessQueue` added as more flexible alternative to `ProcessPool` but without pre-created pool workers

**Breaking Changes**

- The return value of the optional `ProcessManager::wait`-callback is now used to determine if the method should
  continue to wait for further children to exit. If a value other than explicitly `false` is returned, it will continue
  to wait.

* * *

## Release [v4.0.0](https://github.com/sweikenb/pcntl/releases/tag/v4.0.0)

**Plan for future releases**

- Introduction of a maintained changelog for each release
- From now on, only major version releases will introduce BC breaks
- Features that are about to be removed in the next major version will be marked with `@deprecated`

**Bugfixes**

- [#4 Bug: very fast/empty forks will exit before the pcntl_wait() can capture it](https://github.com/sweikenb/pcntl/issues/4)

**Breaking Changes**

- PHP v8.2 as minimum requirement
- Due to incompatibility, this library DOES NOT work if the **grpc** PHP-extension is installed
- Removal of the optional `EventDispatcher`
- Some methods of the `ProcessPool` and `ProcessManager` has been renamed
