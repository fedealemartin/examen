# examen_ciudades
 app de ciudades y provincias

# levantar el servicio

cd examen
php -S localhost:7000 -t public

# para dar de alta nuevas provincias por consola
php bin/console app:add-provincia

# para dar de alta nuevas ciudades por consola
php bin/console app:add-ciudad

# cargar ejemplos a la BD
php bin/console doctrine:fixtures:load
