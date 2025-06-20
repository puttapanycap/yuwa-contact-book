# สมุดโทรศัพท์ภายในองค์กร (Corporate Phonebook)

ระบบสมุดโทรศัพท์ภายในองค์กรที่พัฒนาด้วย PHP, MySQL, Bootstrap 5 และ jQuery สำหรับจัดการข้อมูลพนักงานและการติดต่อภายในองค์กร

## คุณสมบัติหลัก

### หน้าบ้าน (Frontend)
- 🔍 **ระบบค้นหาแบบ Real-time** - ค้นหาข้อมูลพนักงานแบบทันที
- 🏢 **ระบบกรองข้อมูล** - กรองตามตึก, ชั้น, หน่วยงาน
- 📱 **Responsive Design** - รองรับการใช้งานบนทุกอุปกรณ์
- 📞 **Click-to-Call** - คลิกเพื่อโทรออกได้ทันที
- 🎨 **UI/UX ที่สวยงาม** - ใช้ Bootstrap 5 และ Font Sarabun

### หน้าหลังบ้าน (Backend/Admin)
- ➕ **เพิ่มข้อมูลพนักงาน** - เพิ่มข้อมูลพนักงานใหม่
- ✏️ **แก้ไขข้อมูล** - แก้ไขข้อมูลพนักงานที่มีอยู่
- 🗑️ **ลบข้อมูล** - ลบข้อมูลพนักงาน
- 📊 **Dashboard** - แสดงสถิติและข้อมูลสรุป
- 📤 **ส่งออกข้อมูล** - ส่งออกข้อมูลเป็นไฟล์ Excel/CSV

## เทคโนโลยีที่ใช้

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.3.0
- **Library**: jQuery 3.6.0
- **Font**: Google Fonts (Sarabun)
- **Icons**: Font Awesome 6.0.0

## ความต้องการของระบบ

### Server Requirements
- PHP 8.0 หรือสูงกว่า
- MySQL 8.0 หรือสูงกว่า
- Apache/Nginx Web Server
- PHP Extensions:
  - PDO
  - PDO_MySQL
  - JSON
  - MBString

### Client Requirements
- เว็บเบราว์เซอร์ที่รองรับ HTML5 และ CSS3
- JavaScript เปิดใช้งาน

## การติดตั้ง

### วิธีที่ 1: ใช้ Installation Script (แนะนำ)

#### สำหรับ Windows:
```batch
install.bat
```

#### สำหรับ Linux/macOS:
```bash
chmod +x install.sh
./install.sh
```

### วิธีที่ 2: ติดตั้งด้วยตนเอง

1. **Clone หรือดาวน์โหลดโปรเจค**
   ```bash
   git clone [repository-url]
   cd corporate-phonebook
   ```

2. **สร้างไฟล์ Environment**
   ```bash
   cp .env-example .env
   ```

3. **แก้ไขการตั้งค่าฐานข้อมูลในไฟล์ .env**
   ```env
   DB_HOST=localhost
   DB_NAME=corporate_phonebook
   DB_USER=your_username
   DB_PASS=your_password
   ```

4. **สร้างฐานข้อมูล**
   ```bash
   mysql -u username -p database_name < database/schema.sql
   ```

5. **ตั้งค่า Web Server**
   
   **PHP Built-in Server (Development):**
   ```bash
   php -S localhost:8000
   ```
   
   **Apache Virtual Host:**
   ```apache
   <VirtualHost *:80>
       DocumentRoot /path/to/corporate-phonebook
       ServerName phonebook.local
       <Directory /path/to/corporate-phonebook>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

## โครงสร้างไฟล์

```
corporate-phonebook/
├── admin/                  # หน้าจัดการระบบ
│   ├── index.php          # Dashboard
│   ├── add.php            # เพิ่มพนักงาน
│   ├── edit.php           # แก้ไขข้อมูล
│   └── manage.php         # จัดการข้อมูล
├── api/                   # API Endpoints
│   └── search.php         # API ค้นหาข้อมูล
├── assets/                # Static Files
│   ├── css/
│   │   └── style.css      # Custom Styles
│   └── js/
│       └── main.js        # JavaScript Functions
├── config/                # Configuration Files
│   └── database.php       # Database Connection
├── database/              # Database Files
│   └── schema.sql         # Database Schema
├── logs/                  # Log Files
├── backups/               # Backup Files
├── index.php              # หน้าแรก
├── .env-example           # Environment Template
├── install.bat            # Windows Installation Script
├── install.sh             # Linux/macOS Installation Script
└── README.md              # คู่มือการใช้งาน
```

## การใช้งาน

### หน้าแรก (index.php)
1. เข้าใช้งานผ่าน URL ของเว็บไซต์
2. ใช้ช่องค้นหาเพื่อค้นหาข้อมูลพนักงาน
3. ใช้ตัวกรองเพื่อกรองข้อมูลตามตึก, ชั้น, หน่วยงาน
4. คลิกที่เบอร์โทรเพื่อโทรออกได้ทันที

### หน้าจัดการระบบ (admin/)
1. เข้าใช้งานผ่าน URL/admin/
2. ดู Dashboard และสถิติต่างๆ
3. เพิ่มพนักงานใหม่ผ่านหน้า "เพิ่มพนักงานใหม่"
4. จัดการข้อมูลพนักงานที่มีอยู่
5. ส่งออกข้อมูลเป็นไฟล์

## API Documentation

### Search API
**Endpoint:** `GET /api/search.php`

**Parameters:**
- `search` (string): คำค้นหาทั่วไป
- `building` (string): ชื่อตึก
- `floor` (string): หมายเลขชั้น
- `department` (string): ชื่อหน่วยงาน

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "first_name": "สมชาย",
      "last_name": "ใจดี",
      "position": "ผู้จัดการทั่วไป",
      "department": "บริหาร",
      "internal_phone": "1001",
      "mobile_phone": "081-234-5678",
      "email": "somchai@company.com",
      "building": "อาคาร A",
      "floor": 5,
      "room_number": "501"
    }
  ],
  "count": 1
}
```

## การปรับแต่งและขยายระบบ

### เพิ่มฟิลด์ข้อมูลใหม่
1. แก้ไข `database/schema.sql` เพื่อเพิ่มคอลัมน์ใหม่
2. อัปเดตฟอร์มใน `admin/add.php` และ `admin/edit.php`
3. แก้ไข API ใน `api/search.php`
4. อัปเดตการแสดงผลใน `assets/js/main.js`

### เพิ่มระบบ Authentication
1. ใช้ตาราง `admin_users` ที่มีอยู่แล้ว
2. สร้างหน้า login/logout
3. เพิ่ม session management
4. ป้องกันหน้า admin ด้วย authentication

### เพิ่มระบบ Backup อัตโนมัติ
1. สร้าง cron job สำหรับ backup ฐานข้อมูล
2. ใช้ไดเรกทอรี `backups/` ที่มีอยู่
3. ตั้งค่า retention policy

## การแก้ไขปัญหา

### ปัญหาที่พบบ่อย

**1. ไม่สามารถเชื่อมต่อฐานข้อมูลได้**
- ตรวจสอบการตั้งค่าในไฟล์ `.env`
- ตรวจสอบว่า MySQL service ทำงานอยู่
- ตรวจสอบ username และ password

**2. หน้าเว็บแสดงข้อผิดพลาด PHP**
- ตรวจสอบ PHP version (ต้อง 8.0+)
- ตรวจสอบ PHP extensions ที่จำเป็น
- ดู error log ใน `logs/` directory

**3. ระบบค้นหาไม่ทำงาน**
- ตรวจสอบ JavaScript console สำหรับ errors
- ตรวจสอบว่า jQuery โหลดสำเร็จ
- ตรวจสอบ API endpoint ใน `api/search.php`

**4. การแสดงผลไม่ถูกต้อง**
- ตรวจสอบ Bootstrap CSS โหลดสำเร็จ
- ตรวจสอบ Google Fonts โหลดสำเร็จ
- Clear browser cache

### Log Files
ระบบจะสร้าง log files ใน directory `logs/`:
- `error.log` - PHP errors
- `access.log` - การเข้าใช้งาน
- `database.log` - Database queries (ถ้าเปิดใช้งาน)

## การรักษาความปลอดภัย

### มาตรการรักษาความปลอดภัย
- ✅ SQL Injection Protection (PDO Prepared Statements)
- ✅ XSS Protection (HTML Escaping)
- ✅ CSRF Protection (สามารถเพิ่มได้)
- ✅ Input Validation
- ✅ Environment Variables สำหรับข้อมูลสำคัญ

### คำแนะนำด้านความปลอดภัย
1. เปลี่ยน default admin password
2. ใช้ HTTPS ในการใช้งานจริง
3. ตั้งค่า file permissions ให้เหมาะสม
4. อัปเดต PHP และ MySQL เป็นประจำ
5. สำรองข้อมูลเป็นประจำ

## การสนับสนุนและการพัฒนา

### การรายงานปัญหา
หากพบปัญหาการใช้งาน กรุณารายงานพร้อมข้อมูลดังนี้:
- PHP Version
- MySQL Version
- Browser และ Version
- Error Messages
- Steps to reproduce

### การพัฒนาต่อ
สามารถพัฒนาเพิ่มเติมได้ในส่วนต่างๆ เช่น:
- ระบบ Authentication ที่สมบูรณ์
- ระบบ Role-based Access Control
- API สำหรับ Mobile App
- ระบบ Import/Export ข้อมูล
- ระบบ Notification
- ระบบ Audit Log

## License

MIT License - สามารถใช้งานและแก้ไขได้อย่างอิสระ

## ผู้พัฒนา

พัฒนาโดย v0 AI Assistant
สำหรับการใช้งานภายในองค์กร

---

**หมายเหตุ:** ระบบนี้พัฒนาขึ้นเพื่อการใช้งานภายในองค์กร กรุณาปรับแต่งให้เหมาะสมกับความต้องการของแต่ละองค์กร
