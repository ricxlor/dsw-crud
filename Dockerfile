FROM php:8.3-apache

# A imagem oficial do PHP não vem com a extensão mysqli habilitada
RUN docker-php-ext-install mysqli
