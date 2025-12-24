# Frontend Testing Steps

## Prerequisites
1. Backend running at `http://localhost:8000`
2. Frontend running at `http://localhost:3000`
3. Database migrated

---

## Test Flow

### 1. Register as Buyer
- Go to `http://localhost:3000`
- Click "Sign Up"
- Fill form (role: buyer)
- Submit

### 2. Browse Products
- Click "Products" in navbar
- Test search bar
- Test price filters
- Test sorting

### 3. View Product Details
- Click any product card
- Check product info displays
- Test quantity selector

### 4. Add to Cart
- Click "Add to Cart"
- Should redirect to cart page
- Test quantity +/- buttons
- Test remove item

### 5. Checkout
- Click "Proceed to Checkout"
- Fill shipping address
- Select payment method
- Click "Place Order"

### 6. View Orders
- Click "Orders" in navbar
- Check order appears
- Check order status

### 7. Profile
- Click "Profile" in navbar
- Check user info displays
- Test logout

---

## Test Seller Flow

### 1. Register as Seller
- Logout
- Register new account (role: seller)

### 2. Seller Dashboard
- Go to `http://localhost:3000/seller`
- Check stats display

### 3. Create Product
- Click "Add Product"
- Fill product form
- Submit
- Check product appears in list

### 4. Manage Products
- Go to `http://localhost:3000/seller/products`
- Test edit button
- Test delete button

### 5. Manage Orders
- Go to `http://localhost:3000/seller/orders`
- Test status dropdown
- Update order status

---

## Test Affiliate

### 1. Affiliate Dashboard
- Login as buyer
- Go to `http://localhost:3000/affiliate`
- Check referral link
- Test copy button
- Check earnings display

---

## Quick Test Checklist

- [ ] Homepage loads
- [ ] Register works
- [ ] Login works
- [ ] Products page loads
- [ ] Product detail page works
- [ ] Add to cart works
- [ ] Cart page works
- [ ] Checkout works
- [ ] Orders page works
- [ ] Profile page works
- [ ] Seller dashboard works
- [ ] Create product works
- [ ] Seller orders works
- [ ] Affiliate page works
- [ ] Logout works

---

## Common Issues

**"No products found"**
- Backend not running
- No products in database
- Check browser console for API errors

**"Please login"**
- Token expired
- Clear localStorage and login again

**API errors**
- Check backend is at `http://localhost:8000`
- Check `.env.local` has correct API URL
