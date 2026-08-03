<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg" width="400" alt="Laravel Logo"></p>

# نظام إدارة المدارس (School Management System) 🏫

نظام متكامل لإدارة المدارس مبني باستخدام إطار العمل **Laravel**. يشمل إدارة الطلاب، المعلمين، الشؤون الأكاديمية (الصفوف والشُعب)، توزيع المعلمين، جداول الحصص، والمزيد.

## 🚀 متطلبات التشغيل (Prerequisites)

تأكد من توفر البرامج التالية على جهازك قبل البدء:
- **PHP** (الإصدار 8.1 أو أحدث)
- **Composer** (لإدارة حزم PHP)
- **Node.js & npm** (لإدارة حزم الـ Frontend)
- **MySQL** (أو أي قاعدة بيانات مدعومة من Laravel)
- **Git** (لاستنساخ المشروع)

---

## 🛠️ خطوات التثبيت والتشغيل (Installation)

اتبع الخطوات التالية لتشغيل المشروع على بيئتك المحلية:

### 1. استنساخ المشروع (Clone the Repository)
قم بفتح موجة الأوامر (Terminal) واكتب الأمر التالي لاستنساخ المشروع:
```bash
git clone https://github.com/USERNAME/REPO_NAME.git
cd REPO_NAME
```
*(لا تنسَ استبدال `USERNAME/REPO_NAME` برابط المستودع الخاص بك)*

### 2. تثبيت الحزم (Install Dependencies)
قم بتثبيت حزم PHP الأساسية عبر Composer:
```bash
composer install
```
ثم قم بتثبيت حزم الـ Frontend وتجميعها:
```bash
npm install
npm run build
```

### 3. إعداد ملف البيئة (Environment Setup)
قم بنسخ ملف الإعدادات الافتراضي:
```bash
cp .env.example .env
```
افتح ملف `.env` وقم بتعديل بيانات قاعدة البيانات لتتطابق مع جهازك:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_system
DB_USERNAME=root
DB_PASSWORD=
```

### 4. إنشاء مفتاح التشفير (Generate Application Key)
```bash
php artisan key:generate
```

### 5. تجهيز قاعدة البيانات (Database Migration)
قم بإنشاء قاعدة بيانات فارغة في MySQL باسم `school_management_system`، ثم نفذ أمر الترحيل لبناء الجداول وإدخال البيانات الأساسية (إن وُجدت):
```bash
php artisan migrate --seed
```

### 6. تشغيل السيرفر المحلي (Run the Development Server)
أخيراً، قم بتشغيل السيرفر:
```bash
php artisan serve
```
يمكنك الآن تصفح المشروع عبر الرابط: [http://localhost:8000](http://localhost:8000)

---

## ✨ المميزات الرئيسية للنظام
- **إدارة المستخدمين:** صلاحيات مخصصة (مدير النظام، معلم، طالب، ولي أمر).
- **الهيكل الأكاديمي:** إدارة (السنوات الدراسية، الفصول، الصفوف، والشعب).
- **التوزيع الأكاديمي:** نظام توزيع معلمين تاريخي (Historical) يعتمد على العام الدراسي.
- **شؤون الطلاب:** تسجيل، ترقية، ونقل الطلاب بين الصفوف.

## 🛡️ الحماية (Security Vulnerabilities)
إذا اكتشفت أي ثغرة أمنية، يرجى التواصل مع مطور النظام مباشرة بدلاً من استخدام الـ Issues العامة.

## 📄 الترخيص (License)
هذا المشروع مفتوح المصدر تحت ترخيص [MIT license](https://opensource.org/licenses/MIT).
