# API TRANSANCTION

## Description
This is an API to fetch transaction.

## Base URL
The base URL for all API requests is: (http://localhost:8000/api/transactions)


## Get All Transactions

### Endpoint


### Parameters

- **Success**: `true`
- **Message**: "List of Transactions"
- **Pagination Params**: (Included in the response)
  - `page`: Page number for pagination

### Response

```json
{
  "success": true,
  "message": "List of Transactions",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "date": "2023-10-02 00:00:00",
        // ... (transaction data structure)
      },
      // ... (additional transaction entries)
    ],
    "first_page_url": "http://localhost:8000/api/transactions?page=1",
    "from": 1,
    "last_page": 1137,
    "last_page_url": "http://localhost:8000/api/transactions?page=1137",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      // ... (pagination links)
      {
        "url": "http://localhost:8000/api/transactions?page=2",
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": "http://localhost:8000/api/transactions?page=2",
    "path": "http://localhost:8000/api/transactions",
    "per_page": 10,
    "prev_page_url": null,
    "to": 10,
    "total": 11368
  },
  "pagination": null
}
```
## Get DETAIL Transactions

### Endpoint

# GET /api/transactions/{transaction_id}
# Parameters
- **Success**: `true`
- **Message**: "Sales details"
```json

{
  "success": true,
  "message": "Sales details",
  "data": [
    {
      "id": 2,
      "date": "2023-10-02 00:00:00",
      // ... (sales details structure)
    }
  ],
  "pagination": null
}
```
## EXAMPLE

# GET ALL DATA
```json
{
    "success": true,
    "message": "List of Transactions",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000026",
                "customer_id": 16223,
                "customer_name": "TOKO ABAS",
                "tax_percentage": 10,
                "tax_amount": 4261,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 38739,
                "paid_amount": 26577,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 2,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000027",
                "customer_id": 16223,
                "customer_name": "TOKO ABAS",
                "tax_percentage": 10,
                "tax_amount": 7185,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 65315,
                "paid_amount": 53153,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 3,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000028",
                "customer_id": 16224,
                "customer_name": "TK ANGGREK",
                "tax_percentage": 10,
                "tax_amount": 2923,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 26577,
                "paid_amount": 26577,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 4,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000029",
                "customer_id": 16225,
                "customer_name": "PAK BOY",
                "tax_percentage": 10,
                "tax_amount": 2923,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 26577,
                "paid_amount": 26577,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 5,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000030",
                "customer_id": 16226,
                "customer_name": "WAHYU LANCAR",
                "tax_percentage": 10,
                "tax_amount": 2923,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 26577,
                "paid_amount": 26577,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 6,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000031",
                "customer_id": 16227,
                "customer_name": "TK MAKANAN SERBA ADA",
                "tax_percentage": 10,
                "tax_amount": 2923,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 26577,
                "paid_amount": 26577,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 7,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000032",
                "customer_id": 16228,
                "customer_name": "WARUNG KAWIS",
                "tax_percentage": 10,
                "tax_amount": 5153,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 46847,
                "paid_amount": 46847,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 8,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000033",
                "customer_id": 16229,
                "customer_name": "FAHMIL",
                "tax_percentage": 10,
                "tax_amount": 4261,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 38739,
                "paid_amount": 8108,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 9,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000034",
                "customer_id": 16230,
                "customer_name": "CASH GT CANVAS BANCAR",
                "tax_percentage": 10,
                "tax_amount": 5153,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 46847,
                "paid_amount": 46847,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            },
            {
                "id": 10,
                "date": "2023-10-02 00:00:00",
                "reference": "DTBNGT.01INVADM01231000035",
                "customer_id": 16231,
                "customer_name": "WR MBAH E POJOK",
                "tax_percentage": 10,
                "tax_amount": 2081,
                "discount_percentage": 0,
                "discount_amount": 0,
                "shipping_amount": 0,
                "total_amount": 18919,
                "paid_amount": 18919,
                "due_amount": 0,
                "status": "completed",
                "payment_status": "Paid",
                "due": null,
                "payment_method": "cash",
                "note": null,
                "depo": "DTBNGT.01",
                "saler": "TBNCVS01"
            }
        ],
        "first_page_url": "http://localhost:8000/api/transactions?page=1",
        "from": 1,
        "last_page": 1137,
        "last_page_url": "http://localhost:8000/api/transactions?page=1137",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http://localhost:8000/api/transactions?page=2",
                "label": "2",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=3",
                "label": "3",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=4",
                "label": "4",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=5",
                "label": "5",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=6",
                "label": "6",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=7",
                "label": "7",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=8",
                "label": "8",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=9",
                "label": "9",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=10",
                "label": "10",
                "active": false
            },
            {
                "url": null,
                "label": "...",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=1136",
                "label": "1136",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=1137",
                "label": "1137",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/transactions?page=2",
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": "http://localhost:8000/api/transactions?page=2",
        "path": "http://localhost:8000/api/transactions",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 11368
    },
    "pagination": null
}
```

# GET DETAIL
  ```json
{
    "success": true,
    "message": "Sales details",
    "data": [
        {
            "id": 2,
            "date": "2023-10-02 00:00:00",
            "reference": "DTBNGT.01INVADM01231000027",
            "customer_id": 16223,
            "customer_name": "TOKO ABAS",
            "tax_percentage": 10,
            "kode_depo": "DTBNGT.01",
            "kode_salesman": "TBNCVS01",
            "tax_amount": 7185,
            "discount_percentage": 0,
            "discount_amount": 0,
            "shipping_amount": 0,
            "total_amount": 65315,
            "paid_amount": 53153,
            "due_amount": 0,
            "status": "completed",
            "payment_status": "Paid",
            "due": null,
            "payment_method": "cash",
            "note": null,
            "customer": {
                "id": 16223,
                "city": "KAB. TUBAN",
                "country": "",
                "address": "DSN PAMER MARGOSOKO BANCAR",
                "name": "TOKO ABAS",
                "email": "",
                "contact": ""
            },
            "depo": {
                "id": 4,
                "Kode": "DTBNGT.01"
            },
            "saler": {
                "id": 31,
                "Kode": "TBNCVS01",
                "Nama": "ANDRI SUYANTI"
            },
            "sale_details": [
                {
                    "product_id": 4073,
                    "product_name": "TEH CAP DANDANG MERAH 1T",
                    "product_code": "M01DDM01",
                    "quantity": 2,
                    "price": 53153.2,
                    "unit_price": 26576.6,
                    "sub_total": 53153,
                    "product_discount_amount": 0,
                    "product_discount_type": "fixed",
                    "dpp": 53153.2,
                    "product_tax_amount": 5847
                },
                {
                    "product_id": 4074,
                    "product_name": "BLACK TEA BOX 25",
                    "product_code": "M02CBB01",
                    "quantity": 3,
                    "price": 12162.2,
                    "unit_price": 4054.05,
                    "sub_total": 12162,
                    "product_discount_amount": 0,
                    "product_discount_type": "fixed",
                    "dpp": 12162.2,
                    "product_tax_amount": 1338
                }
            ]
        }
    ],
    "pagination": null
}