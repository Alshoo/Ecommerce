# Simple Store API

## Overview
The Simple Store API is a RESTful API built with Laravel, designed to manage a simple e-commerce platform. It supports user authentication, product management, cart functionality, order processing, and more. The API allows developers to create applications that interact with the e-commerce data effectively.

## Features
- **User Authentication:** Using Laravel Sanctum for API token management.
- **Product Management:** CRUD operations for products, including categories and details.
- **Cart Management:** Add, update, and delete items from the shopping cart.
- **Order Processing:** Handle orders and their details.
- **Favorites Management:** Save products to favorites for quick access.
- **Comment System:** Users can leave comments on products.
- **Notifications:** Send and manage user notifications.

## Requirements
- PHP >= 8.1
- Laravel Framework 10.x
- MySQL (or any other database supported by Laravel)

## Installation

### Clone the Repository
```bash
git clone https://github.com/Ziad-Abaza/simple-store-api.git
cd simple-store-api
```

### Install Dependencies
Make sure you have [Composer](https://getcomposer.org/) installed. Then run:
```bash
composer install
```

### Environment Configuration
1. Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Update the `.env` file with your database and application configurations:
   ```env
   APP_NAME=Laravel
   APP_ENV=local
   APP_KEY=base64:YOUR_APP_KEY
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecommerce
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Generate Application Key
Run the following command to generate the application key:
```bash
php artisan key:generate
```

### Run Migrations
Run the migrations to create the necessary database tables:
```bash
php artisan migrate
```

### Start the Development Server
Start the Laravel development server:
```bash
php artisan serve
```
The API will be accessible at `http://localhost:8000`.

## API Documentation
### Authentication
- **Register User:** `POST /api/register`
- **Login User:** `POST /api/login`
- **Logout User:** `POST /api/logout`

### Products
- **List Products:** `GET /api/products`
- **Create Product:** `POST /api/products`
- **Show Product:** `GET /api/products/{id}`
- **Update Product:** `POST /api/products/{id}`
- **Delete Product:** `DELETE /api/products/{id}`

### Cart
- **View Cart:** `GET /api/cart`
- **Add to Cart:** `POST /api/cart`
- **Update Cart Item:** `POST /api/cart/{id}`
- **Remove from Cart:** `DELETE /api/cart/{id}`

### Orders
- **List Orders:** `GET /api/order-details`
- **Create Order:** `POST /api/order-details`
- **Show Order:** `GET /api/order-details/{id}`
- **Update Order:** `POST /api/order-details/{id}`
- **Delete Order:** `DELETE /api/order-details/{id}`

### Favorites
- **List Favorites:** `GET /api/favorite`
- **Add to Favorites:** `POST /api/favorite`
- **Remove from Favorites:** `DELETE /api/favorite/{id}`

### Comments
- **List Comments:** `GET /api/comment`
- **Add Comment:** `POST /api/comment`
- **Update Comment:** `POST /api/comment/{id}`
- **Delete Comment:** `DELETE /api/comment/{id}`

