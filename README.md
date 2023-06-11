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

что в динамике высчитываем:
купленные лекции LectureService::getAllPurchasedLecturesIdsAndTheirDatesByUser
цены на категории, промо пак, все лекции -> аксессор Lecture::prices.

идеи как поменять: хранить в отдельных таблицах, по изменению цены лекции/промопака/категории пересчитывать ->
диспатчить ивент/job пересчитывать -> апдейтить значения в таблицах. И оттуда уже брать эту инфу и отдавать фронту

где диспатчим -> админка:
изменение цены общей в подкатегории -> пересчитываем цены всех лекций в категории, цены за всепак лекций
//TODO Дописать это
