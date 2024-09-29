
# Module Package for Laravel 11

This package provides a simple way to create and manage modules in Laravel 11. It offers a modular architecture that allows developers to separate functionality into independent modules, making the application more scalable and maintainable.

## Features

- **Create Modules**: Easily generate new modules using a command.
- **Manage Module Status**: Enable or disable modules through the `enabled_modules.json` file.
- **Auto Register Routes**: Automatically load web and API routes for each module.
- **Versioning Support**: Track module versions via the `module.json` file.
- **Migration Support**: Automatically register and run migrations for modules.
- **Customizable Module Directory**: You can set a custom directory for storing modules via the `module.php` configuration file.
- **View All Modules**: Use a command to list all modules with their status and version.

## Installation

1. Add the package to your `composer.json` file:

   ```bash
   composer require smony/module
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --tag=config
   ```

3. Create your first module using the built-in command:

   ```bash
   php artisan make:module {ModuleName}
   ```

   This will create a new module with its own structure, including routes, controllers, migrations, and more.

## Basic Usage

### Creating a Module

To create a new module, use the following command:

```bash
php artisan make:module {ModuleName}
```

This will create a folder inside the `Modules` directory (or a custom directory, if configured) with the following structure:

```
Modules/
    ModuleName/
        Controllers/
        Routes/
        Database/
        Providers/
        module.json
```

### Viewing All Modules

You can list all modules, their status, and their version using:

```bash
php artisan module:list
```

This command will display a table with information about each module.

### Enable or Disable a Module

Modules are managed through the `enabled_modules.json` file located in the root directory. To disable a module, simply set its status to `false`:

```json
{
    "ModuleName": true,
    "AnotherModule": false
}
```

### Customizing Module Directory

By default, modules are created inside the `Modules` directory. You can change this by updating the `module.php` configuration file.

## Future Plans

- Add more customizable commands for module creation.
- Improve the versioning system and compatibility checking.
- Add tests for better package stability.
- Add more features like modular middleware and more advanced routing.

## Contributing

Feel free to contribute to this project! Fork, create a feature branch, and submit a pull request.

## License

This package is open-sourced software licensed under the MIT license.
