# Changelog

All notable changes to `mateffy/laravel-introspect` will be documented in this file.

## 1.1.2 (_2025-06-29_)
- fixed `view:cache` failing due to missing view folder by removing `->hasViews()` from the service provider

## 1.1.1 (_2025-05-22_)
- fixed `class_*` methods failing if classes don't exist (spatie pro packages for example)

## 1.1.0 (_2025-05-22_)
- added missing methods to `RouteQueryInterface`
- added `Introspect::routes()` method to Facade DocBlock
- added `artisan introspect:views` command for querying from the command line
- improved model query performance
- class methods `whereImplements()`, `whereExtends()` and `whereUses()` now accept multiple values and work correctly with models

## 1.0.2 (_2025-05-18_)
- removed Livewire dependency (for `invade()` function) in favor of `spatie/invade`


## 1.0.1 (_2025-05-18_)
- Oopsie composer.json was broken, that's now fixed
- So this is the actual release of the package lol

## 1.0.0 (_2025-05-18_)
- Initial release of the package
