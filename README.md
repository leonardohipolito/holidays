## About Holidays

It's a simple REST Api to manage holidays

## Install

Start APP
```bash
docker run --name holidays-manager -p 8000:8000 -d leonardohipolito/holidays:latest
```
Generate APP KEY
```bash
docker exec -it holidays-manager php artisan key:generate
```
Migrate and Seed Database
```bash
docker exec -it holidays-manager php artisan migrate:fresh --seed
```
Generate User Token
```bash
docker exec -it holidays-manager php artisan make:token
```


## Documentation

[http://localhost:8000/swagger](http://localhost:8000/swagger)

## Tests

To test app you can run:

```bash
docker exec -it holidays-manager php artisan test
```