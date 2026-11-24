Coro ngerun e lur

```
git clone https://github.com/Kelompok-5-D3TIB-25/kliniku/
cd kliniku
git config core.fileMode false
sudo chmod -R 777 .
```
Setelah ini :
Backend :
```
cd backend
docker compose up -d
```
buka docker desktop > containers > kliniku atau jalankan `docker compose logs -f`
tunggu sampai ada seperti `nginx:ready to handle connections`, atau jika ada error terkait artisan atau yang lain. itu berarti dah jalan

setelah itu
```
sudo chmod -R 777 .
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh
```

kemudian buka `localhost:8080` (bukan `8000`)
nek wis njedul laravel 13 berarti uwis kudune
