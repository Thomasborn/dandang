# API PRODUCTS

## Description
This is an API to fetch products.

## Base URL
The base URL for all API requests is: [http://omahit.my.id/api/products](http://localhost:8000/api/products)

## Endpoints
# Create a New Product API

## Endpoint

- **POST /api/products**

## Description

This API endpoint allows you to create a new product.

## Request

- **Method:** POST
- **Headers:** 
  - Content-Type: application/json
- **Body:** 
  - JSON object representing the new product information.


### Request Body Example

```json
{
  "name": "new product",
  "type": "Product",
  "description": "This is a new product description.",
  "image": "storage/barang/new_product.png",
  "size": "0",
  "uom": "grams",
  "price": "1500000",
  "stock": "50"
}


## Response

### Success Response

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 2,
    "name": "new product",
    "type": "Product",
    "description": "This is a new product description.",
    "image": "storage/barang/new_product.png",
    "size": "0",
    "uom": "grams",
    "price": "1500000",
    "stock": "50"
  }
}

Errors
Success but have wrong response:


{
  "success": false,
  "message": "Error message goes here"
}
This API uses the following error codes:

400 Bad Request: The request was malformed or missing required parameters.
401 Unauthorized: The API key provided was invalid or missing.
404 Not Found: The requested resource was not found.
500 Internal Server Error: An unexpected error occurred on the server.