# Custom Popup Alert System Implementation Guide

## Files Created
1. `assets/js/alert-popup.js` - Main JavaScript library
2. `assets/css/alert-popup.css` - Styling for popups
3. `example_with_popups.php` - Example implementation

## How to Implement in Your Files

### 1. Add CSS and JS to HTML Head/Body

```html
<!-- Add to <head> section -->
<link rel="stylesheet" href="assets/css/alert-popup.css">

<!-- Add before closing </body> tag -->
<script src="assets/js/alert-popup.js"></script>
```

### 2. Replace Default Alerts

#### Old Way:
```javascript
alert('Success message');
if (confirm('Are you sure?')) {
    // do something
}
```

#### New Way:
```javascript
// Success Alert
showSuccess('บันทึกข้อมูลสำเร็จ', 'สำเร็จ', () => location.reload());

// Error Alert  
showError('เกิดข้อผิดพลาด', 'ข้อผิดพลาด');

// Warning Alert
showWarning('กรุณาตรวจสอบข้อมูล', 'คำเตือน');

// Info Alert
showInfo('ข้อมูลทั่วไป', 'ประกาศ');

// Confirm Dialog
showConfirm('คุณแน่ใจหรือไม่?', 'ยืนยัน', 
    () => {
        // On confirm
        deleteItem();
    }, 
    () => {
        // On cancel (optional)
        console.log('Cancelled');
    }
);
```

### 3. Update PHP CRUD Files

#### Old Way:
```php
echo "<script>alert('Success'); window.location='page.php';</script>";
```

#### New Way:
```php
echo "<script src='assets/js/alert-popup.js'></script>";
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        showSuccess('บันทึกข้อมูลสำเร็จ', 'สำเร็จ', () => window.location='page.php');
    });
</script>";
```

### 4. Available Functions

- `showSuccess(message, title, callback)` - Green success popup
- `showError(message, title, callback)` - Red error popup  
- `showWarning(message, title, callback)` - Yellow warning popup
- `showInfo(message, title, callback)` - Blue info popup
- `showConfirm(message, title, onConfirm, onCancel)` - Confirmation dialog

### 5. Features

✅ **Beautiful Bootstrap 5 Modals** - Professional looking popups
✅ **Icon Support** - FontAwesome icons for each type
✅ **Callback Functions** - Execute code after user clicks OK
✅ **Responsive Design** - Works on all devices
✅ **Animation Effects** - Smooth fade and scale animations
✅ **Customizable** - Easy to modify colors and styles
✅ **Thai Language Support** - All text in Thai
✅ **No Dependencies** - Only requires Bootstrap 5

### 6. Quick Implementation for All Files

To quickly update all your existing files:

1. Add the CSS/JS includes to all HTML files
2. Replace `alert()` calls with `showSuccess()` or `showError()`
3. Replace `confirm()` calls with `showConfirm()`
4. Update PHP files to use the new popup system

### 7. Example Usage in CRUD Operations

```javascript
function deleteItem(id) {
    showConfirm('คุณแน่ใจหรือไม่ที่จะลบรายการนี้?', 'ยืนยันการลบ', () => {
        fetch('delete.php', {
            method: 'POST',
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message, 'สำเร็จ', () => location.reload());
            } else {
                showError(data.message, 'เกิดข้อผิดพลาด');
            }
        });
    });
}
```

This popup system provides a much better user experience compared to default browser alerts and maintains consistency across your entire application.