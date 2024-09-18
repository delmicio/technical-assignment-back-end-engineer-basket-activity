## Basket API Project

This project is a Laravel-based API for managing user baskets, products, and removed items. It allows users to add or
remove products from their baskets and provides a way for the sales team to download a CSV file of removed items.

## Table of Contents

1. [Getting Started for Developers](#getting-started-for-developers)
    * [Prerequisites](#prerequisites)
    * [Installation](#installation)
    * [Running the Application](#running-the-application)
    * [Database Migration and Seeding](#database-migration-and-seeding)
2. [Downloading CSV for Sales Team](#downloading-csv-for-sales-team)
    * [Using the Web Interface](#using-the-web-interface)
    * [Using the API Endpoint](#using-the-api-endpoint)
3. [API Documentation](#api-documentation)

---

## Getting Started for Developers

### Prerequisites

* **Docker**: Make sure Docker is installed on your machine. [Download Docker here](https://www.docker.com/get-started).

### Installation

1. **Clone the Repository**

   ```plaintext
   git clone https://github.com/madewithlove/technical-assignment-back-end-engineer-delmicio.git
   cd basket-activity
   ```

2. **Install Dependencies**

   Use Composer via Docker to install PHP dependencies:

   ```plaintext
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install
   ```

3. **Copy** `**.env**` **File**

   ```plaintext
   cp .env.example .env
   ```

4. **Install Laravel Sail**

   ```plaintext
   php artisan sail:install
   ```

### Running the Application

1. **Start Docker Containers**

   ```plaintext
   ./vendor/bin/sail up -d
   ```

   This command starts the application in detached mode.

2. **Generate Application Key**

   ```plaintext
   ./vendor/bin/sail artisan key:generate
   ```

3. **Configure Database**

   Ensure your `.env` file has the correct database settings for MySQL provided by Sail.

### Database Migration and Seeding

1. **Run Migrations**

   ```plaintext
   ./vendor/bin/sail artisan migrate
   ```

2. **Seed the Database**

   ```plaintext
   ./vendor/bin/sail artisan db:seed
   ```

   This will populate the database with sample users and products.

### Accessing the Application

* **Web Interface**

  Open your browser and navigate to [http://localhost](http://localhost) to view the application.

* **API Endpoints**

  The API is accessible at [http://localhost/api/v1](http://localhost/api/v1).

## Downloading CSV for Sales Team

### Using the Web Interface

1. **Navigate to the Application**

   Go to [http://localhost](http://localhost) in your web browser.

2. **Use the Download CSV Button**
    * At the top of the page, you'll find the "From" and "To" date fields.
    * Enter the desired date range for the removed items.
    * Click the **"Download CSV"** button to download the file containing the removed items within the specified date
      range.

### Using the API Endpoint

You can also download the CSV file directly via the API.

* **Endpoint**

  ```plaintext
  GET http://localhost/api/v1/removed-items/export-csv
  ```

* **Query Parameters**
    * `from` (optional): Start date in `YYYY-MM-DD` format.
    * `to` (optional): End date in `YYYY-MM-DD` format.
* **Example**

  To download removed items from September 1, 2023, to September 10, 2023:

  ```plaintext
  GET http://localhost/api/v1/removed-items/export-csv?from=2023-09-01&to=2023-09-10
  ```

* **Notes**
    * The endpoint will return a CSV file containing `User ID`, `Product ID`, and `Product Name`.

## API Documentation

All API endpoints are documented in the `openapi.yaml` file located at the root of the project.

* **Viewing the Documentation**

  You can use [Swagger UI](https://swagger.io/tools/swagger-ui/) or [Redoc](https://github.com/Redocly/redoc) to
  visualize the API documentation.

* **Importing into Postman**

  Import the `openapi.yaml` file into Postman to interact with the API endpoints directly.

---

Feel free to contribute to this project or report any issues you encounter.