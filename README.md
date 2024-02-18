Api docs: [https://roddom1.vip/api-docs](https://roddom1.vip/api-docs)

Install deps:

```
php composer install --no-dev
```

Эти сбилденные штуки используются только на странице логина api docs:

```
npm i
npm run build
```

Generate api.json for api documentation:

```
php ./vendor/bin/openapi app -o public/docs/PKcfe0Ueow7fwhTNpVhY.json
```

### Some ways to solve problems with filament (admin panel engine) 'view' files slowness:

```
php artisan icons:cache
```

at dev env:

```
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

Open config/debugbar.php
Change views value from true to false
