# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 3.0.4 - 2019-03-21
### Fixed
- Forward merge `2.x`

## 3.0.3 - 2019-01-21
### Removed
- Call to `loadClassCache` in `Kernel`, this call is deprecated and will be removed in Symfony 4

## 3.0.2 - 2019-01-03
### Fixed
- Use `static::` for debug-envs in `Kernel`, not `self::` as they cannot be overwritten that way

## 3.0.1 - 2018-10-08
### Fixed
- Disable rendering of HTML in 404 of static media

## 3.0.0 - 2018-06-21
### Added
- Support for Symfony 3.x
### Removed
- Support for Symfony 2.x

## 2.1.0 - 2019-03-21
### Added
- `RedisSessionHandler` to resolve the passing of https://github.com/zikula/NativeSession

## 2.0.2 - 2018-10-08
### Fixed
- Disable rendering of HTML in 404 of static media

## 2.0.0 - 2018-02-28
### Changed
- Drop support for php 5.6.
- Delete deprecated BaseKernel and fix corresponding unit tests.

## 1.5.8 - 2018-10-08
### Fixed
- Disable rendering of HTML in 404 of static media

## 1.5.5 - 2018-04-30
### Fixed
- Fix for percent-encoded characters in filename passed through the static media handler

## 1.5.4 - 2017-08-09
### Fixed
- Fixed bug in console command flags fixed in version 1.5.3

## 1.5.3 - 2017-08-08
### Fixed
- Fixed missing support for --env and --debug flags for console commands

## 1.5.2 - 2017-04-12
### Fixed
- Fixed Kernel parameter order for backward compatibility

## 1.5.1 - 2017-04-12
### Fixed
- Fixed regexp in for static content

## 1.5.0 - 2017-04-10
### Changed
- Changed the order of the 'named' constructor parameter to support backwards compatibility with Symfony creating kernels too.

## 1.4.0
### Added
- Add a feature where the kernel can be 'named' via a constructor parameter. 
  This way one app can support multiple types of kernels. See doc/ for more info.

## 1.3.0
### Fixed
- Maintenance release containing only CS and dependency fixes
