###Laravel 5.x:

Add service provider to the 'providers' array in config/app.php:

```
Gavrya\Componizer\Integration\Laravel\ComponizerServiceProvider::class,
```

Add facade alias to the 'aliases' array in config/app.php:

```
'ComponizerEditor' => Gavrya\Componizer\Integration\Laravel\ComponizerEditor::class,
```
