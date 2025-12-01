# Driver Real-Time Location Tracking Guide

## Overview
This system allows you to track drivers in real-time using Firebase Realtime Database. Drivers' availability status is automatically displayed in the admin panel based on their presence in Firebase.

## Features Implemented

### 1. Test Drivers Created ✅
- **8 test drivers** have been seeded into the database
- Each driver has a car assigned with different categories (Economy, Comfort, Luxury)
- All drivers are in "active" status and "approved" state

### 2. Firebase Integration ✅
The system now connects to Firebase Realtime Database to:
- Fetch driver locations in real-time
- Check driver availability status
- Display driver location on interactive maps

### 3. Driver Availability Display ✅

#### In Drivers List (index page):
- New "Availability" column shows online/offline status
- 🟢 **Green badge "Online"** = Driver exists in Firebase (available for rides)
- ⚫ **Gray badge "Offline"** = Driver NOT in Firebase (not available)

#### In Driver Details (show page):
- Availability badge at the top of driver info
- **If driver is online:** Shows interactive map with real-time location
- **If driver is offline:** Shows "Offline" badge, no map displayed
- Location updates automatically every 5 seconds

### 4. Real-Time Location Map ✅
When viewing a driver's details page:
- Interactive map using Leaflet.js
- Shows driver's current location with a green marker
- Map auto-updates every 5 seconds
- Smooth marker animation when location changes
- Popup shows driver name and last update time

## Firebase Data Structure

Your Firebase Realtime Database should have this structure:

```json
{
  "drivers": {
    "24": {
      "bearing": 0,
      "latitude": 32.5768306,
      "longitude": 35.8604178,
      "timestamp": 1234567890
    },
    "25": {
      "bearing": 45.5,
      "latitude": 24.7136,
      "longitude": 46.6753,
      "timestamp": 1234567891
    }
  }
}
```

**Key Points:**
- The driver ID in Firebase should match the driver's ID in your database
- `latitude` and `longitude` are required fields
- `bearing` is optional (direction the driver is facing)
- `timestamp` is optional but recommended

## How It Works

### 1. Driver Goes Online (From Mobile App)
When a driver opens your mobile app and goes online:
```javascript
// Mobile app writes to Firebase
firebase.database().ref('drivers/' + driverId).set({
  latitude: currentLat,
  longitude: currentLng,
  bearing: currentBearing,
  timestamp: Date.now()
});
```

### 2. Admin Panel Checks Availability
- When you open the drivers list, the system checks Firebase for each driver
- If driver exists in Firebase → Shows as "Online" 🟢
- If driver doesn't exist in Firebase → Shows as "Offline" ⚫

### 3. Real-Time Location Updates
- When viewing driver details, the map fetches location every 5 seconds
- If driver goes offline, the availability badge updates automatically
- No page refresh needed

## Testing the Feature

### Option 1: Using Firebase Console
1. Go to Firebase Console → Realtime Database
2. Manually add a driver with this structure:
```json
{
  "drivers": {
    "24": {
      "latitude": 24.7136,
      "longitude": 46.6753,
      "bearing": 0,
      "timestamp": 1733085600000
    }
  }
}
```
3. Replace `24` with any driver ID from your database
4. Refresh the drivers list in admin panel
5. You'll see driver #24 as "Online" 🟢
6. Click on driver details to see the map

### Option 2: Using Mobile App
1. Install your driver mobile app
2. Login with test driver credentials:
   - Email: `ahmed.driver@test.com`
   - Password: `password123`
3. Go online in the app (should write to Firebase)
4. Check admin panel to see real-time updates

## Test Driver Credentials

All test drivers have the same password: `password123`

| ID | Name | Email | Phone |
|----|------|-------|-------|
| (varies) | Ahmed Al-Rashid | ahmed.driver@test.com | +966501234567 |
| (varies) | Mohammed Al-Fahd | mohammed.driver@test.com | +966501234568 |
| (varies) | Khalid Al-Otaibi | khalid.driver@test.com | +966501234569 |
| (varies) | Omar Al-Harbi | omar.driver@test.com | +966501234570 |
| (varies) | Faisal Al-Mutairi | faisal.driver@test.com | +966501234571 |
| (varies) | Abdullah Al-Shammari | abdullah.driver@test.com | +966501234572 |
| (varies) | Saeed Al-Dosari | saeed.driver@test.com | +966501234573 |
| (varies) | Yasser Al-Ghamdi | yasser.driver@test.com | +966501234574 |

*Note: Driver IDs will vary based on your database. Check your `users` table for actual IDs.*

## Files Modified/Created

### New Files:
1. `database/seeders/CarDataSeeder.php` - Seeds car categories, models, and types
2. `DRIVER_TRACKING_GUIDE.md` - This documentation file

### Modified Files:
1. `app/Services/FirebaseService.php` - Added driver location methods
2. `app/Http/Controllers/DriverController.php` - Added location fetching and availability checks
3. `routes/web.php` - Added route for location API
4. `resources/views/drivers/index.blade.php` - Added availability column
5. `resources/views/drivers/show.blade.php` - Added map and real-time tracking
6. `database/seeders/TestDriversSeeder.php` - Added more test drivers

## API Endpoints

### Get Driver Location (AJAX)
```
GET /admin/drivers/{driver}/location
```

**Response (Online):**
```json
{
  "success": true,
  "location": {
    "latitude": 24.7136,
    "longitude": 46.6753,
    "bearing": 0,
    "timestamp": 1733085600000,
    "is_available": true,
    "driver_id": 24
  },
  "is_available": true
}
```

**Response (Offline):**
```json
{
  "success": false,
  "message": "Driver location not available",
  "is_available": false
}
```

## Troubleshooting

### Driver shows as offline but is online
1. Check Firebase Console - is the driver in the database?
2. Verify the driver ID matches between your database and Firebase
3. Check Firebase connection in `.env` file

### Map not displaying
1. Check browser console for JavaScript errors
2. Verify Leaflet.js is loading correctly
3. Ensure driver has valid latitude/longitude in Firebase

### Location not updating
1. Check if the location API is accessible: `/admin/drivers/{id}/location`
2. Verify Firebase Service Account JSON file exists
3. Check Laravel logs: `storage/logs/laravel.log`

## Mobile App Integration

Your mobile app should update Firebase when:
1. Driver goes online/offline
2. Driver location changes (every 5-10 seconds while online)
3. Driver accepts/completes a ride

**Example mobile implementation (Flutter/React Native):**
```javascript
// When driver goes online
firebase.database().ref('drivers/' + driverId).set({
  latitude: position.coords.latitude,
  longitude: position.coords.longitude,
  bearing: position.coords.heading || 0,
  timestamp: Date.now()
});

// Update location periodically
setInterval(() => {
  if (isOnline) {
    updateDriverLocation();
  }
}, 5000); // Every 5 seconds

// When driver goes offline
firebase.database().ref('drivers/' + driverId).remove();
```

## Next Steps

1. **Add Firebase data for test drivers** to see them as online
2. **Integrate with your mobile app** to automatically update Firebase
3. **Test the real-time updates** by changing driver locations in Firebase
4. **Monitor Firebase usage** to ensure it's within your plan limits

## Support

If you encounter any issues:
1. Check `storage/logs/laravel.log` for backend errors
2. Check browser console for JavaScript errors
3. Verify Firebase credentials and database URL in your `.env` file

---

**Created:** December 1, 2025
**System:** Laravel + Firebase Realtime Database + Leaflet.js Maps

