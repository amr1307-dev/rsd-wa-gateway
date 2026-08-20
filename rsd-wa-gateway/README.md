# 🚀 RED SEA DIGITAL — WhatsApp Multi-Device Socket Gateway

خادم ربط الواتساب المستقل وخفيف الوزن (Zero-Config Socket Gateway) المطور خصيصاً لمنظومة `redsea-ai-engine`.

---

## ⚡ طرق التشغيل الفوري (Deployment Options)

### الخيار 1: تشغيل فوري مجاني بضغطة زر على Render.com (Recommended - 1-Click Free Cloud):
1. ارفع هذا المجلد إلى مستودع GitHub خاص بك.
2. توجه إلى [Render.com](https://render.com/) وأنشئ **New Web Service**.
3. اختر المستودع وقم بتعيين:
   - **Environment**: `Node`
   - **Build Command**: `npm install`
   - **Start Command**: `node server.js`
4. في قسم **Environment Variables** أضف:
   - `API_KEY`: `rsd_secret_token_2026`
   - `WEBHOOK_URL`: `https://redseadigital.pro/wp-json/rsd/v1/whatsapp-webhook`
5. بعد انتهاء النشر، ستحصل على رابط مباشر مثل: `https://rsd-wa-gateway.onrender.com`.
6. قم بنسخ هذا الرابط ولصقه في لوحة تحكم ووردبريس (تبويب CRM) في حقل **Socket / Gateway API URL**.

---

### الخيار 2: التشغيل عبر Docker على أي VPS:
```bash
cd rsd-wa-gateway
docker compose up -d
```

---

### الخيار 3: التشغيل المحلي المباشر عبر Node.js:
```bash
cd rsd-wa-gateway
npm install
node server.js
```

---

## 🔑 بيانات الربط الافتراضية في ووردبريس:
- **Socket Gateway API URL**: `https://your-gateway-domain.com` (أو `http://localhost:3000` عند التجربة)
- **Instance Name**: `rsd_live`
- **API Secret Key**: `rsd_secret_token_2026`