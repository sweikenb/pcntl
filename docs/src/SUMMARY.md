# Summary

# Installation

Install the latest version using [composer](https://getcomposer.org/):

```bash
composer require sweikenb/pcntl
```

## System Requirements

This library requires at least **PHP v8.2** with the following extensions enabled:

- `pcntl`
- `posix`
- `sockets`

Note that this library is **incompatible** with the `grpc` extension!

In order to execute the unit-tests you will need to run them in a linux environment.

# Features

- [Process Manager](features/process-manager.md)
- [Process Queue](features/process-queue.md)
- [Inter Process Communication (IPC)](features/ipc.md)

# Examples

- [Async Workloads](examples/async-workloads.md)
- [Queued Parallel-Processing](examples/queued-processing.md)
- [IPC Examples](examples/ipc-examples.md)
