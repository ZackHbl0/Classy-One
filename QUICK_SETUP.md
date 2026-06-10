# Quick Setup Guide - Dynamic URLs

## 🚀 Fast Track Setup (5 minutes)

### Step 1: Run the Setup Script
```bash
setup-dynamic-urls.bat
```

This will:
- Clear all Laravel caches
- Verify storage symlink
- Check .env configuration
- Verify middleware registration
- Show your machine's IP address

### Step 2: Verify .env
Ensure this line exists in your `.env` file:
```env
APP_URL=http://localhost:8000
```

### Step 3: Recreate Storage Symlink (if needed)
If you had the old symlink cached:
```bash
rmdir public\storage
php artisan storage:link
```

### Step 4: Start Laravel Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Important:** Use `--host=0.0.0.0` to make it accessible from your phone!

### Step 5: Find Your IP Address
```bash
ipconfig
```
Look for "IPv4 Address" (e.g., `192.168.100.99`)

### Step 6: Update Flutter App
In your Flutter app, update:
```dart
static const String baseUrl = 'http://YOUR_IP_HERE:8000';
// Example: 'http://192.168.100.99:8000'
```

---

## ✅ Testing

### Test Web Dashboard:
1. Open: `http://localhost:8000/admin`
2. Upload a file or view existing media
3. Verify no 403 FORBIDDEN errors

### Test Mobile App:
1. Ensure phone is on same WiFi
2. Login to the app
3. Check courses, events, documents load correctly

---

## 🎯 What Was Fixed?

✅ Web dashboard uses `localhost:8000`  
✅ Mobile app automatically gets your IP address  
✅ No more APP_URL conflicts  
✅ Works on any network without code changes  

---

## 📚 Detailed Documentation

For complete technical details, see: **DYNAMIC_URL_SOLUTION.md**

---

## 🔧 Manual Commands (if script fails)

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recreate storage symlink
rmdir public\storage
php artisan storage:link

# Start server
php artisan serve --host=0.0.0.0 --port=8000
```

---

## ❓ Troubleshooting

**Problem:** Web shows 403 FORBIDDEN  
**Fix:** Ensure `APP_URL=http://localhost:8000` and recreate storage symlink

**Problem:** Mobile can't access media  
**Fix:** Use your machine's IP (not localhost) in Flutter baseUrl

**Problem:** Can't connect from phone  
**Fix:** Ensure server is running with `--host=0.0.0.0` and firewall allows port 8000

---

## 📞 Need Help?

Read the full documentation: `DYNAMIC_URL_SOLUTION.md`
