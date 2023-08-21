# Livt Commands

This package give simple and easy commands for a Laravel, Inertia, Vue, Tailwind project.

It also includes a `livt:install` command to install all the packages you need for a Laravel, Inertia, Vue, Tailwind project, without Jetstream or Breeze, giving you more control over your project.

## Requirements
- Laravel 10.x or higher
- NPM
- Node

## Installation

You can install the package via composer:

```bash
composer require --dev elijahcruz/livt-commands
```

## Usage

### livt:install

This command will install the packages you need for the Livt stack, it'll even install Ziggy for you. It is meant to be used on a fresh Laravel installation.

```bash
php artisan livt:install
```

### make:page

This command will create a new page in `resources/js/Pages`. You can use / in the name to create subdirectories, or you can also use the dot notation.

```base
// This works
php artisan make:page page.name

// This also works
php artisan make:page Page/Name

// Even this works
php artisan make:page Page.Name

// And also this
php artisan make:page Page/Name/With/More/Names

// Want to create a component? Use the --component flag.
// It'll use the resources/js/Pages folder instead.
php artisan make:page MyAwesome.Component --component
```