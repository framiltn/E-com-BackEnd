# Complete API Endpoints Reference

## Base URL
```
http://localhost:8000/api
```

---

## 🔓 Public Endpoints (No Auth)

### Authentication
- `POST /register` - Register new user
- `POST /login` - Login user

### Products
- `GET /products` - List products (with search, filters, pagination)
- `GET /products/{id}` - Get product details

### Categories
- `GET /categories` - List categories
- `GET /categories/{id}` - Get category
- `GET /categories/slug/{slug}` - Get by slug

### Reviews
- `GET /products/{id}/reviews` - Product reviews

### CMS
- `GET /banners` - Homepage banners
- `GET /pages` - Static pages
- `GET /pages/{slug}` - Get page
- `GET /faqs` - FAQs

### Webhooks
- `POST /webhooks/shiprocket` - Shiprocket webhook

---

## 🔐 Authenticated Endpoints

### User
- `GET /user` - Current user info
- `POST /logout` - Logout

### Cart
- `POST /cart/add` - Add to cart
- `GET /cart` - View cart
- `POST /cart/update` - Update quantity
- `POST /cart/remove` - Remove item

### Checkout & Payment
- `POST /checkout` - Process checkout
- `POST /payment/create-order` - Create Razorpay order
- `POST /payment/verify` - Verify payment

### Orders
- `GET /orders` - List my orders
- `GET /orders/{id}` - Order details
- `POST /orders/{id}/status` - Update status
- `POST /orders/{id}/cancel` - Cancel order
- `GET /orders/{id}/track` - Track shipment

### Wishlist
- `GET /wishlist` - View wishlist
- `POST /wishlist` - Add to wishlist
- `DELETE /wishlist/{productId}` - Remove

### Reviews
- `POST /reviews` - Create review
- `GET /reviews/my` - My reviews

### Refunds
- `GET /refunds` - List refunds
- `POST /refunds` - Request refund
- `GET /refunds/{id}` - Refund details

### Disputes
- `GET /disputes` - My disputes
- `POST /disputes` - Create dispute
- `GET /disputes/{id}` - Dispute details
- `POST /disputes/{id}/messages` - Add message

### Coupons
- `POST /coupons/validate` - Validate coupon

### Notifications
- `GET /notifications` - All notifications
- `GET /notifications/unread` - Unread only
- `POST /notifications/{id}/read` - Mark as read
- `POST /notifications/read-all` - Mark all read

### Affiliate
- `GET /affiliate` - My affiliate profile
- `GET /affiliate/referrals` - My referrals
- `GET /affiliate/tree` - Affiliate tree view
- `GET /affiliate/offers` - My offers
- `GET /affiliate/offers/eligibility` - Check eligibility
- `POST /affiliate/offers/share-coupon` - Share coupon

### Payouts
- `GET /payouts` - Payout history
- `POST /payouts` - Request payout

---

## 👨‍💼 Seller Endpoints

### Products
- `POST /seller/products` - Create product
- `GET /seller/products` - My products
- `PUT /seller/products/{id}` - Update product
- `DELETE /seller/products/{id}` - Delete product

### Product Images
- `POST /seller/products/{id}/images` - Upload image
- `DELETE /seller/products/{productId}/images/{imageId}` - Delete image
- `POST /seller/products/{productId}/images/{imageId}/primary` - Set primary

### Orders
- `GET /seller/orders` - My orders
- `GET /seller/orders/{id}` - Order details
- `POST /seller/orders/{id}/status` - Update status
- `GET /seller/analytics` - Sales analytics

### Store Settings
- `GET /seller/settings` - Get settings
- `PUT /seller/settings` - Update settings
- `POST /seller/settings/logo` - Upload logo
- `POST /seller/settings/banner` - Upload banner

### Seller Application
- `POST /seller/apply` - Apply to become seller

### Offers
- `POST /seller/offers` - Create affiliate offer

---

## 👑 Admin Endpoints

### Dashboard
- `GET /admin/dashboard` - Admin metrics

### Seller Applications
- `GET /admin/applications` - Pending applications
- `POST /admin/applications/{id}/approve` - Approve
- `POST /admin/applications/{id}/reject` - Reject

### Product Approvals
- `GET /admin/products/pending` - Pending products
- `POST /admin/products/{id}/approve` - Approve
- `POST /admin/products/{id}/reject` - Reject

### Disputes
- `GET /admin/disputes` - All disputes
- `POST /admin/disputes/{id}/resolve` - Resolve

### Banners
- `POST /admin/banners` - Create banner
- `PUT /admin/banners/{id}` - Update banner
- `DELETE /admin/banners/{id}` - Delete banner

### Pages
- `POST /admin/pages` - Create page
- `PUT /admin/pages/{id}` - Update page

### FAQs
- `POST /admin/faqs` - Create FAQ
- `PUT /admin/faqs/{id}` - Update FAQ
- `DELETE /admin/faqs/{id}` - Delete FAQ

### Offers
- `POST /admin/offers` - Create affiliate offer

---

## 📝 Request Examples

### Register
```json
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "buyer"
}
```

### Login
```json
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Create Product
```json
POST /api/seller/products
Authorization: Bearer {token}
{
  "name": "Product Name",
  "description": "Description",
  "price": 1500,
  "stock": 100,
  "category_id": 1,
  "brand": "Brand Name",
  "commission_level": "9-6-3"
}
```

### Add to Cart
```json
POST /api/cart/add
Authorization: Bearer {token}
{
  "product_id": 1,
  "quantity": 2
}
```

### Checkout
```json
POST /api/checkout
Authorization: Bearer {token}
{
  "coupon_code": "SAVE10",
  "shipping_address": {
    "name": "John Doe",
    "address": "123 Main St",
    "city": "Mumbai",
    "state": "Maharashtra",
    "pincode": "400001",
    "phone": "9876543210",
    "email": "john@example.com"
  }
}
```

### Create Dispute
```json
POST /api/disputes
Authorization: Bearer {token}
{
  "order_id": 1,
  "reason": "Product damaged",
  "description": "The product arrived damaged..."
}
```

---

## 🔑 Authentication

All authenticated endpoints require Bearer token:
```
Authorization: Bearer {your_token_here}
```

Get token from `/register` or `/login` response.

---

## 📊 Response Format

### Success Response
```json
{
  "message": "Success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "message": "Error message",
  "errors": { ... }
}
```

### Paginated Response
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

---

## 🎯 Total Endpoints: 80+

- Public: 12
- Authenticated: 25
- Seller: 15
- Admin: 18
- Affiliate: 6
- Webhooks: 1
