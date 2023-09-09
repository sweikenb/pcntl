# Changelog

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
- Removal of the optional EventDispatcher
- Some methods of the PoolManager and ProcessManager has been renamed
