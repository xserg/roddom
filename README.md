Api docs: [https://api.мамы.онлайн/docs](https://api.xn--80axb4d.online/docs/).

```cd api.mamy.online```

Install deps:
```
php8.2 ../.local/bin/composer install --no-dev
```

Generate api.json for api documentation:
```
php8.2 ./vendor/bin/openapi app -o public/docs/api.json
```
