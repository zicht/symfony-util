# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 1.5.4 - 2018-04-30
### Fixed
- Fix for percent-encoded characters in filename passed through the static media handler

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
