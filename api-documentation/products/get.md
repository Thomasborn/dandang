# API PRODUCTS

## Description
This is an API to fetch products.

## Base URL
The base URL for all API requests is: [http://omahit.my.id/api/products](http://localhost:8000/api/products)

## Endpoints

### GET /products
Returns a list of all products in the library.

#### Parameters
- `limit` (optional): The maximum number of products to return. Default is none.
- `offset` (optional): The number of products to skip before starting to return results. Default is 0.

#### Response
Returns a JSON object with the following properties:
- `success` (boolean): Indicates whether the request was successful or not.
- `message` (string): A message providing information about the request result.
- `data` (array): An array containing product information.
  - `id` (integer): The unique identifier for the product.
  - `name` (string): The name of the product.
  - `type` (string): The type of the product.
  - `description` (string): A detailed description of the product.
  - `image` (string): The URL or path to the product image.
  - `size` (string): The size of the product.
  - `uom` (string): The unit of measure for the product.
  - `price` (string): The price of the product.
  - `stock` (string): The current stock quantity of the product.

#### Example
**Request:**

**Response:**
```json
{
  "success": true,
  "message": "List Data Products",
  "data": [
    {
      "id": 1,
      "name": "dandang selection",
      "type": "Product",
      "description": "Dandang Selection merupakan daun teh pilihan yang diolah tradisional. Seduhan teh pekat dan aroma wangi daun teh pilihan \"daging daun teh lebih tebal dari daging daun teh biasa\"",
      "image": "storage/barang/6571f01299e9a_dandang selection.png",
      "size": "0",
      "uom": "grams",
      "price": "2000000",
      "stock": "32"
    },
    // ... (other product entries)
  ]
}
Errors
Success but have wrong response:
{
  "success": false,
  "message": "Error message goes here"
}

Certainly! Below is a Markdown representation of the provided API documentation:

markdown
Copy code
# API PRODUCTS

## Description
This is an API to fetch products.

## Base URL
The base URL for all API requests is: [http://localhost:8000/api/products](http://localhost:8000/api/products)

## Endpoints

### GET /products
Returns a list of all products in the library.

#### Parameters
- `limit` (optional): The maximum number of products to return. Default is none.
- `offset` (optional): The number of products to skip before starting to return results. Default is 0.

#### Response
Returns a JSON object with the following properties:
- `success` (boolean): Indicates whether the request was successful or not.
- `message` (string): A message providing information about the request result.
- `data` (array): An array containing product information.
  - `id` (integer): The unique identifier for the product.
  - `name` (string): The name of the product.
  - `type` (string): The type of the product.
  - `description` (string): A detailed description of the product.
  - `image` (string): The URL or path to the product image.
  - `size` (string): The size of the product.
  - `uom` (string): The unit of measure for the product.
  - `price` (string): The price of the product.
  - `stock` (string): The current stock quantity of the product.

#### Example
**Request:**
GET /products?limit=5&offset=10

swift
Copy code

**Response:**
```json
{
  "success": true,
  "message": "List Data Products",
  "data": [
    {
      "id": 1,
      "name": "dandang selection",
      "type": "Product",
      "description": "Dandang Selection merupakan daun teh pilihan yang diolah tradisional. Seduhan teh pekat dan aroma wangi daun teh pilihan \"daging daun teh lebih tebal dari daging daun teh biasa\"",
      "image": "storage/barang/6571f01299e9a_dandang selection.png",
      "size": "0",
      "uom": "grams",
      "price": "2000000",
      "stock": "32"
    },
    // ... (other product entries)
  ]
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