# HubFlow Release

## Summary

HubFlow Release is a utility that facilitates the generation of a [HubFlow](https://datasift.github.io/gitflow/)
release for projects that manage the version of the application via a "package.json" file.

This utility is clearly relevant to a very narrow slice of the Internet but
comes in very handy on all of my side projects.

## Dependencies

* [Composer](https://getcomposer.org/) - Used for installation.
* [PHP](http://php.net/downloads.php)

## Installation

```bash
composer global require "dascentral/hubflow-release"
```

Make sure to place the `$HOME/.composer/vendor/bin` directory (or the equivalent directory for your OS)
in your `$PATH` so the executable can be located by your system.

## Usage

```bash
hf release [major|minor|patch]
```

A "patch" release is assumed when the second argument is ommitted.

## Actions Performed

1. A HubFlow release is started
1. The version in the "package.json" is incremented as instructed
1. The change to the "package.json" is committed to the release branch
1. The HubFlow release is finished