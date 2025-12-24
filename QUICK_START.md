# 🚀 Quick Start Guide - E-Commerce Marketplace

## Prerequisites
- Node.js 18+ installed
- PHP 8.2+ installed
- PostgreSQL installed
- Composer installed

---

## Backend Setup (5 minutes)

### 1. Navigate to Backend
```bash
cd d:\MyDocs\E-Com-Ori\BackEnd\marketplace-backend
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Run Setup Script
```bash
.\setup-complete.ps1
```

This will:
- Run migrations
- Create storage link
- Create admin user
- Clear caches

### 4. Start Backend Server
```bash
php artisan serve
```

Backend runs at: **http://localhost:8000**

### 5. Start Queue Worker (New Terminal)
```bash
php artisan queue:work
```

---

## Frontend Setup (3 minutes)

### 1. Navigate to Frontend
```bash
cd d:\MyDocs\E-Com-Ori\FrontEnd
```

### 2. Install Dependencies
```bash
npm install
```

### 3. Start Frontend Server
```bash
npm run dev
```

Frontend runs at: **http://localhost:3000**

---

## 🎉 You're Ready!

### Access the Application
- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8000/api
- **API Docs:** http://localhost:8000/api/documentation

### Default Login Credentials

**Admin:**
- Email: `admin@marketplace.com`
- Password: `admin123`

**Test Seller:**
- Email: `seller@test.com`
- Password: `password123`

**Test Buyer:**
- Email: `buyer@test.com`
- Password: `password123`

---

## 📱 Test the Application

### 1. Register a New User
- Go to http://localhost:3000/register
- Fill in the form
- Choose "Buy Products" or "Sell Products"
- Click Sign Up

### 2. Browse Products
- Go to http://localhost:3000/products
- Use search and filters
- Click on a product to view details

### 3. Add to Cart
- Click "Add to Cart" on any product
- Go to http://localhost:3000/cart
- Update quantities
- Proceed to checkout

### 4. Seller Features
- Register as seller
- Wait for admin approval
- Add products
- Manage orders

### 5. Affiliate Program
- After first order, you become an affiliate
- Get your referral link
- Share with others
- Earn commissions (6-4-2, 9-6-3, or 12-8-4)

---

## 🛠️ Troubleshooting

### Backend Issues

**Port 8000 already in use:**
```bash
php artisan serve --port=8001
```
Update frontend `.env.local` to `http://localhost:8001/api`

**Database connection error:**
- Check PostgreSQL is running
- Verify credentials in `.env`

**Queue not processing:**
```bash
php artisan queue:restart
php artisan queue:work
```

### Frontend Issues

**Port 3000 already in use:**
```bash
npm run dev -- -p 3001
```

**API connection error:**
- Ensure backend is running
- Check `.env.local` has correct API URL

**Module not found:**
```bash
rm -rf node_modules
npm install
```

---

## 📚 Documentation

- **Backend Features:** `BackEnd/marketplace-backend/IMPLEMENTATION_COMPLETE.md`
- **API Reference:** `BackEnd/marketplace-backend/API_ENDPOINTS.md`
- **Frontend README:** `FrontEnd/README.md`
- **Project Summary:** `COMPLETION_SUMMARY.md`

---

## 🎯 What's Implemented

### Backend (100%)
✅ Authentication & Authorization
✅ Multi-vendor System
✅ Product Management
✅ Shopping Cart
✅ Order Management
✅ 3-Level Affiliate Marketing
✅ Payment Integration (Razorpay)
✅ Shipping Integration (Shiprocket)
✅ Admin Panel
✅ Seller Dashboard
✅ Reviews & Ratings
✅ Coupons & Discounts
✅ Refunds & Disputes
✅ Wishlist
✅ CMS (Banners, Pages, FAQs)

### Frontend (40%)
✅ Home Page
✅ Product Listing
✅ Login & Register
✅ Shopping Cart
✅ Navbar & Footer
⏳ Product Details
⏳ Checkout
⏳ Orders Page
⏳ Seller Dashboard
⏳ Admin Panel
⏳ Affiliate Dashboard

---

## 🚀 Next Development Steps

1. **Complete Product Details Page**
2. **Implement Checkout Flow**
3. **Add Orders Management**
4. **Build Seller Dashboard**
5. **Create Admin Panel UI**
6. **Add Affiliate Dashboard**
7. **Integrate Razorpay Payment**
8. **Add Image Upload UI**
9. **Implement Notifications**
10. **Mobile Responsive Design**

---

## 💡 Tips

- Keep both backend and frontend running simultaneously
- Use browser DevTools to debug API calls
- Check backend logs for errors: `storage/logs/laravel.log`
- Use Postman collection for API testing
- Frontend hot-reloads on file changes

---

## 🎊 Success!

Your marketplace is now running locally. Start building the remaining frontend pages and test the complete flow!

**Happy Coding! 🚀**
