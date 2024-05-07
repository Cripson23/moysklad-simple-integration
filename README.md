# Проект "Заказы покупателей Мой склад"

## Описание проекта

Проект "Заказы покупателей Мой склад" предназначен для интеграции с системой "Мой склад". Основная функциональность включает авторизацию, получение и обработку заказов, а также управление статусами заказов.

## Технологии

- **PHP 8.1**: Основной язык программирования.
- **Redis 7.0**: Система кеширования для ускорения загрузки данных.
- **Composer**: Менеджер зависимостей для PHP.

## Развертывание проекта

### 1. Установка пакетов и настройка рабочего окружения
На хост-машине, способом, совместимым с вашей ОС (пример с ubuntu):
- Установите **PHP 8.1** и базовые модули
```
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring
```
- Установите **Redis 7.0**
```
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server.service
```
- Установите **composer**
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Клонирование репозитория и установка зависимостей
- Склонируйте репозиторий проекта в директорию на своей хост-машине
```
git clone https://amgroup-repository-url.git
cd amgroup-project-directory
```

- Установите зависимости через Composer
```
composer install
```

### 3. Настройка файла .env
- Скопируйте файл **.env.example** в файл **.env**, который будет содержать конфигурацию для вашего окружения:
```
cp .env.example .env
```
- Заполните **.env** файл актуальными значениями конфигурации

### 4. Настройка веб-сервера
- После настройки всех компонентов, убедитесь, что веб-сервер (**apache2** / **nginx**) настроен для обслуживания проекта и PHP корректно настроен для выполнения.
- Рабочая директория проекта **./public**
- Файл точки входа **./public/index.php**
#### Пример настройки **nginx**
- Создание файла конфигурации:
```
sudo nano /etc/nginx/sites-available/amgroup-site
```
- Базовое содержимое конфигурации:
```
server {
    listen 80;
    server_name 192.168.1.100;  # Актуальный IP-адрес сервера

    root /var/www/amgroup-site/public;  # Корректный путь к корневой директории проекта
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
- Создание символической ссылки в **/etc/nginx/sites-enabled**
```
sudo ln -s /etc/nginx/sites-available/amgroup-site /etc/nginx/sites-enabled/
```
- Проверка корректности конфигурации nginx:
```
sudo nginx -t
```
- Перезапуск nginx для применения изменений:
```
sudo systemctl reload nginx
```

