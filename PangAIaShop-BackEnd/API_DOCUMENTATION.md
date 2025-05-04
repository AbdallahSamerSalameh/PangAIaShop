# PangAIa Shop API Documentation

This document provides information about the API endpoints available for the PangAIa Shop e-commerce platform.

## Base URL

All API endpoints are relative to the base URL:

```
http://your-domain.com/api
```

During development, this will typically be:

```
http://localhost:8000/api
```

## Authentication

The API uses Laravel Sanctum for token-based authentication. 

### Register a new user

**Endpoint:** `POST /register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2025-04-25T10:00:00.000000Z",
      "updated_at": "2025-04-25T10:00:00.000000Z"
    },
    "access_token": "1|abcdefghijklmnopqrstuvwxyz12345",
    "token_type": "Bearer"
  }
}
```

### Login

**Endpoint:** `POST /login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2025-04-25T10:00:00.000000Z",
      "updated_at": "2025-04-25T10:00:00.000000Z"
    },
    "access_token": "2|abcdefghijklmnopqrstuvwxyz67890",
    "token_type": "Bearer"
  }
}
```

### Authenticated Requests

For all authenticated routes, include the token in the Authorization header:

```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz12345
```

### Logout

**Endpoint:** `POST /logout`

**Headers:**
```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz12345
```

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

## Products

### List All Products

**Endpoint:** `GET /products`

**Query Parameters:**
- `search` - Search term for product name and description
- `category_id` - Filter by category ID
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `sort_by` - Field to sort by (name, price, created_at)
- `sort_direction` - Sort direction (asc, desc)
- `per_page` - Number of items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product Name",
        "description": "Product description",
        "price": 99.99,
        "average_rating": 4.5,
        "images": [
          {
            "id": 1,
            "product_id": 1,
            "url": "http://example.com/images/product1.jpg",
            "is_primary": true
          }
        ],
        "categories": [
          {
            "id": 1,
            "name": "Category Name"
          }
        ]
      }
    ],
    "first_page_url": "http://localhost:8000/api/products?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "http://localhost:8000/api/products?page=10",
    "links": [],
    "next_page_url": "http://localhost:8000/api/products?page=2",
    "path": "http://localhost:8000/api/products",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 150
  }
}
```

### Get Product Details

**Endpoint:** `GET /products/{product_id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product Name",
    "description": "Product description",
    "price": 99.99,
    "average_rating": 4.5,
    "images": [
      {
        "id": 1,
        "product_id": 1,
        "url": "http://example.com/images/product1.jpg",
        "is_primary": true
      }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Category Name"
      }
    ],
    "variants": [
      {
        "id": 1,
        "product_id": 1,
        "name": "Size",
        "value": "Large",
        "price": 109.99
      }
    ],
    "reviews": [
      {
        "id": 1,
        "product_id": 1,
        "user_id": 2,
        "rating": 5,
        "title": "Great product",
        "comment": "This is a great product, highly recommended!",
        "created_at": "2025-04-20T10:00:00.000000Z"
      }
    ]
  }
}
```

## Categories

### List All Categories

**Endpoint:** `GET /categories`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Electronic devices and gadgets",
      "children": [
        {
          "id": 2,
          "name": "Smartphones",
          "slug": "smartphones",
          "description": "Mobile phones and accessories"
        }
      ]
    }
  ]
}
```

### Get Products by Category

**Endpoint:** `GET /categories/{category_id}/products`

**Query Parameters:**
- Same as List All Products

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product Name",
        "description": "Product description",
        "price": 99.99,
        "average_rating": 4.5,
        "images": [
          {
            "id": 1,
            "product_id": 1,
            "url": "http://example.com/images/product1.jpg",
            "is_primary": true
          }
        ]
      }
    ],
    "first_page_url": "http://localhost:8000/api/categories/1/products?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost:8000/api/categories/1/products?page=5",
    "links": [],
    "next_page_url": "http://localhost:8000/api/categories/1/products?page=2",
    "path": "http://localhost:8000/api/categories/1/products",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
  }
}
```

## Cart Management

### Get Cart Contents

**Endpoint:** `GET /cart`

**Headers:**
```
Authorization: Bearer your-auth-token
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subtotal": 199.98,
    "tax": 20.00,
    "shipping_fee": 10.00,
    "discount": 0,
    "total": 229.98,
    "promo_code": null,
    "items": [
      {
        "id": 1,
        "cart_id": 1,
        "product_id": 1,
        "variant_id": null,
        "quantity": 2,
        "price": 99.99,
        "product": {
          "id": 1,
          "name": "Product Name",
          "images": [
            {
              "id": 1,
              "url": "http://example.com/images/product1.jpg",
              "is_primary": true
            }
          ]
        }
      }
    ]
  }
}
```

### Add to Cart

**Endpoint:** `POST /cart/add`

**Headers:**
```
Authorization: Bearer your-auth-token
```

**Request Body:**
```json
{
  "product_id": 1,
  "variant_id": null,
  "quantity": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product added to cart",
  "data": {
    "id": 1,
    "user_id": 1,
    "subtotal": 99.99,
    "tax": 10.00,
    "shipping_fee": 10.00,
    "discount": 0,
    "total": 119.99,
    "promo_code": null,
    "items": [
      {
        "id": 1,
        "cart_id": 1,
        "product_id": 1,
        "variant_id": null,
        "quantity": 1,
        "price": 99.99,
        "product": {
          "id": 1,
          "name": "Product Name",
          "images": [
            {
              "id": 1,
              "url": "http://example.com/images/product1.jpg",
              "is_primary": true
            }
          ]
        }
      }
    ]
  }
}
```

## Orders

### Place an Order

**Endpoint:** `POST /orders`

**Headers:**
```
Authorization: Bearer your-auth-token
```

**Request Body:**
```json
{
  "shipping_address": "123 Main St, New York, NY 10001",
  "billing_address": "123 Main St, New York, NY 10001",
  "payment_method": "credit_card",
  "promo_code": "SUMMER10"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order placed successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "subtotal": 199.98,
    "tax": 20.00,
    "shipping_fee": 10.00,
    "discount": 20.00,
    "total": 209.98,
    "status": "pending",
    "shipping_address": "123 Main St, New York, NY 10001",
    "billing_address": "123 Main St, New York, NY 10001",
    "payment_method": "credit_card",
    "promo_code": "SUMMER10",
    "created_at": "2025-04-25T10:00:00.000000Z",
    "updated_at": "2025-04-25T10:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 1,
        "variant_id": null,
        "quantity": 2,
        "price": 99.99,
        "subtotal": 199.98,
        "product": {
          "id": 1,
          "name": "Product Name",
          "images": [
            {
              "id": 1,
              "url": "http://example.com/images/product1.jpg",
              "is_primary": true
            }
          ]
        }
      }
    ]
  }
}
```

## Implementing in React

Here's an example of how to use these APIs in your React frontend:

```jsx
// Example: Login and fetch products

import axios from 'axios';
import { useState, useEffect } from 'react';

// Create axios instance with base URL
const api = axios.create({
  baseURL: 'http://localhost:8000/api'
});

// Add a request interceptor to include the token in all authenticated requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Login function
  const login = async (email, password) => {
    try {
      setLoading(true);
      const response = await api.post('/login', { email, password });
      if (response.data.success) {
        localStorage.setItem('token', response.data.data.access_token);
        localStorage.setItem('user', JSON.stringify(response.data.data.user));
        setIsLoggedIn(true);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'An error occurred');
    } finally {
      setLoading(false);
    }
  };

  // Fetch products
  const fetchProducts = async () => {
    try {
      setLoading(true);
      const response = await api.get('/products');
      if (response.data.success) {
        setProducts(response.data.data.data);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'An error occurred');
    } finally {
      setLoading(false);
    }
  };

  // Add to cart
  const addToCart = async (productId, quantity = 1) => {
    try {
      setLoading(true);
      const response = await api.post('/cart/add', {
        product_id: productId,
        quantity: quantity
      });
      // Handle success (e.g., show notification)
      console.log('Added to cart:', response.data);
    } catch (err) {
      setError(err.response?.data?.message || 'An error occurred');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    // Check if user is already logged in
    const token = localStorage.getItem('token');
    if (token) {
      setIsLoggedIn(true);
    }
    
    // Fetch products on page load
    fetchProducts();
  }, []);

  return (
    <div className="App">
      {/* Your components here */}
    </div>
  );
}

export default App;
```

## Recommended React Packages

For your React frontend, consider using these packages:

1. **axios** - For making HTTP requests
2. **react-router-dom** - For routing
3. **react-query** - For data fetching, caching, and state management
4. **redux** or **zustand** - For global state management
5. **tailwindcss** - For styling (optional)
6. **formik** or **react-hook-form** - For form handling
7. **yup** - For form validation
8. **react-toastify** or **react-hot-toast** - For notifications

## Security Considerations

1. Always use HTTPS in production
2. Store tokens securely (HttpOnly cookies or localStorage for SPAs)
3. Include CSRF protection
4. Implement token refresh mechanism
5. Set appropriate CORS headers on the server