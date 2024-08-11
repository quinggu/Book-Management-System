# book-management-system

docker-compose build
docker-compose up -d

docker-compose exec app php bin/console doctrine:migrations:status
docker-compose exec app php bin/console doctrine:migrations:migrate

docker-compose exec app php bin/console doctrine:fixtures:load

upewnić się, że mamy public/uploads


rejestracja:
http://localhost:8000/register

logowanie:
http://localhost:8000/login

strona główna z wyszukiwarką:
http://localhost:8000/books

dodawanie nowej książki:
http://localhost:8000/book/new

widok książki:
http://localhost:8000/book/1

strona użytkownika:
http://localhost:8000/user/3/books

edycja książki:
http://localhost:8000/book/3/edit

usuwwanie:
http://localhost:8000/book/3