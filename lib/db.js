import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const DATA_DIR = path.join(__dirname, '..', 'data');
const DB_FILE = path.join(DATA_DIR, 'db.json');

// Default initial dataset from create_database.sql
const DEFAULT_DATA = {
  sequences: {
    category_id: 4,
    shipping_id: 4,
    price_id: 13,
    age_group_id: 11,
    runner_id: 5,
    reg_id: 4,
    payment_id: 3,
    checkin_id: 1
  },
  categories: [
    { category_id: 1, name: 'Mini Marathon', distance_km: 5.0, start_time: '06:00:00', time_limit: '01:30:00', giveaway_type: 'เสื้อ + เหรียญ', created_at: '2025-01-10 08:00:00' },
    { category_id: 2, name: 'Half Marathon', distance_km: 21.1, start_time: '05:30:00', time_limit: '03:00:00', giveaway_type: 'เสื้อ + เหรียญ + ถ้วย', created_at: '2025-01-10 08:00:00' },
    { category_id: 3, name: 'Full Marathon', distance_km: 42.2, start_time: '05:00:00', time_limit: '06:00:00', giveaway_type: 'เสื้อ + เหรียญ + ถ้วย + ใบประกาศ', created_at: '2025-01-10 08:00:00' },
  ],
  shipping_options: [
    { shipping_id: 1, type: 'Pickup', cost: 0.00, detail: 'รับด้วยตัวเองที่งาน วันที่ 15-16 มีนาคม 2025', created_at: '2025-01-10 08:00:00' },
    { shipping_id: 2, type: 'EMS', cost: 50.00, detail: 'จัดส่งทางไปรษณีย์ EMS ภายใน 7-10 วันทำการ', created_at: '2025-01-10 08:00:00' },
    { shipping_id: 3, type: 'Kerry', cost: 45.00, detail: 'จัดส่งผ่าน Kerry Express ภายใน 3-5 วันทำการ', created_at: '2025-01-10 08:00:00' },
  ],
  price_rates: [
    { price_id: 1, category_id: 1, runner_type: 'Standard', amount: 500.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 2, category_id: 1, runner_type: 'Senior 70+', amount: 400.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 3, category_id: 1, runner_type: 'Disabled', amount: 300.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 4, category_id: 1, runner_type: 'Student', amount: 350.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 5, category_id: 2, runner_type: 'Standard', amount: 800.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 6, category_id: 2, runner_type: 'Senior 70+', amount: 650.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 7, category_id: 2, runner_type: 'Disabled', amount: 500.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 8, category_id: 2, runner_type: 'Student', amount: 600.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 9, category_id: 3, runner_type: 'Standard', amount: 1200.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 10, category_id: 3, runner_type: 'Senior 70+', amount: 1000.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 11, category_id: 3, runner_type: 'Disabled', amount: 800.00, created_at: '2025-01-10 08:00:00' },
    { price_id: 12, category_id: 3, runner_type: 'Student', amount: 900.00, created_at: '2025-01-10 08:00:00' },
  ],
  age_groups: [
    { age_group_id: 1, category_id: 1, gender: 'M', min_age: 18, max_age: 29, label: 'ชาย 18-29 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 2, category_id: 1, gender: 'M', min_age: 30, max_age: 39, label: 'ชาย 30-39 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 3, category_id: 1, gender: 'M', min_age: 40, max_age: 49, label: 'ชาย 40-49 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 4, category_id: 1, gender: 'M', min_age: 50, max_age: 59, label: 'ชาย 50-59 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 5, category_id: 1, gender: 'M', min_age: 60, max_age: 100, label: 'ชาย 60+ ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 6, category_id: 1, gender: 'F', min_age: 18, max_age: 29, label: 'หญิง 18-29 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 7, category_id: 1, gender: 'F', min_age: 30, max_age: 39, label: 'หญิง 30-39 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 8, category_id: 1, gender: 'F', min_age: 40, max_age: 49, label: 'หญิง 40-49 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 9, category_id: 1, gender: 'F', min_age: 50, max_age: 59, label: 'หญิง 50-59 ปี', created_at: '2025-01-10 08:00:00' },
    { age_group_id: 10, category_id: 1, gender: 'F', min_age: 60, max_age: 100, label: 'หญิง 60+ ปี', created_at: '2025-01-10 08:00:00' },
  ],
  runners: [
    { runner_id: 1, first_name: 'สมชาย', last_name: 'ใจดี', date_of_birth: '1995-05-15', gender: 'Male', citizen_id: '1100501234567', phone: '0812345678', email: 'somchai@gmail.com', address: '123/45 ถ.สุขุมวิท กรุงเทพฯ 10110', is_disabled: 0, created_at: '2025-01-12 10:00:00' },
    { runner_id: 2, first_name: 'สมศรี', last_name: 'มั่งมี', date_of_birth: '1998-08-20', gender: 'Female', citizen_id: '1100701987654', phone: '0898765432', email: 'somsri@gmail.com', address: '99/1 ถ.พหลโยธิน เชียงใหม่ 50000', is_disabled: 0, created_at: '2025-01-12 11:30:00' },
    { runner_id: 3, first_name: 'วัฒนา', last_name: 'พัฒนา', date_of_birth: '1975-03-10', gender: 'Male', citizen_id: '3100600112233', phone: '0865554321', email: 'wattana@yahoo.com', address: '45 หมู่ 3 ต.ในเมือง ขอนแก่น 40000', is_disabled: 0, created_at: '2025-01-13 09:15:00' },
    { runner_id: 4, first_name: 'กิตติพงษ์', last_name: 'สดใส', date_of_birth: '2001-11-25', gender: 'Male', citizen_id: '1103700445566', phone: '0824443322', email: 'kittipong@hotmail.com', address: '88 ถ.เพชรเกษม นครปฐม 73000', is_disabled: 1, created_at: '2025-01-14 14:20:00' },
  ],
  registrations: [
    { reg_id: 1, runner_id: 1, category_id: 1, price_id: 1, shipping_id: 1, reg_date: '2025-01-15', shirt_size: 'L', status: 'Paid', bib_number: 'M001', checkin_status: 1, checkin_time: '2025-03-15 08:30:00', created_at: '2025-01-15 10:30:00' },
    { reg_id: 2, runner_id: 2, category_id: 2, price_id: 5, shipping_id: 2, reg_date: '2025-01-16', shirt_size: 'M', status: 'Paid', bib_number: 'H001', checkin_status: 0, checkin_time: null, created_at: '2025-01-16 11:00:00' },
    { reg_id: 3, runner_id: 3, category_id: 3, price_id: 9, shipping_id: 3, reg_date: '2025-01-17', shirt_size: 'XL', status: 'Pending', bib_number: null, checkin_status: 0, checkin_time: null, created_at: '2025-01-17 15:45:00' },
  ],
  payments: [
    { payment_id: 1, reg_id: 1, total_amount: 500.00, payment_time: '2025-01-15 10:45:00', payment_method: 'QR Code', status: 'Success', transaction_ref: 'QR-20250115-001', slip_url: null, created_at: '2025-01-15 10:45:00' },
    { payment_id: 2, reg_id: 2, total_amount: 850.00, payment_time: '2025-01-16 11:20:00', payment_method: 'Bank Transfer', status: 'Success', transaction_ref: 'SCB-88992211', slip_url: null, created_at: '2025-01-16 11:20:00' },
  ],
  checkins: [
    { checkin_id: 1, reg_id: 1, checked_by: 'Staff 01', checkin_time: '2025-03-15 08:30:00', note: 'รับเบอร์วิ่งและเสื้อไซส์ L แล้ว' }
  ],
  settings: {
    event_name: 'RUNNING 2025',
    event_subtitle: 'งานวิ่งมาราธอนเพื่อสุขภาพและการกุศล ประจำปี 2025',
    event_date: '2025-03-15',
    event_location: 'สวนพุทธมณฑล จ.นครปฐม',
    promptpay_number: '0812345678',
    promptpay_name: 'งานวิ่งมาราธอน Running 2025',
    bank_name: 'ธนาคารกสิกรไทย (KBANK)',
    bank_account_no: '123-4-56789-0',
    bank_account_name: 'งานวิ่งเพื่อสุขภาพและการกุศล Running 2025',
    admin_pin: '1234'
  }
};

let db = null;

function ensureDataDir() {
  if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
  }
}

export function loadDatabase() {
  ensureDataDir();
  if (fs.existsSync(DB_FILE)) {
    try {
      const raw = fs.readFileSync(DB_FILE, 'utf8');
      db = JSON.parse(raw);
      // Validate structure & fill missing arrays if any
      if (!db.categories) db.categories = DEFAULT_DATA.categories;
      if (!db.shipping_options) db.shipping_options = DEFAULT_DATA.shipping_options;
      if (!db.price_rates) db.price_rates = DEFAULT_DATA.price_rates;
      if (!db.age_groups) db.age_groups = DEFAULT_DATA.age_groups;
      if (!db.runners) db.runners = DEFAULT_DATA.runners;
      if (!db.registrations) db.registrations = DEFAULT_DATA.registrations;
      if (!db.payments) db.payments = DEFAULT_DATA.payments;
      if (!db.checkins) db.checkins = DEFAULT_DATA.checkins || [];
      if (!db.settings) db.settings = DEFAULT_DATA.settings;
      if (!db.sequences) db.sequences = DEFAULT_DATA.sequences;
      console.log('Database loaded from persistent storage');
      return db;
    } catch (e) {
      console.error('Failed to parse db.json, initializing default data', e);
    }
  }
  
  db = JSON.parse(JSON.stringify(DEFAULT_DATA));
  saveDatabase();
  console.log('Initialized fresh database file');
  return db;
}

export function getDatabase() {
  if (!db) {
    loadDatabase();
  }
  return db;
}

export function saveDatabase() {
  ensureDataDir();
  try {
    fs.writeFileSync(DB_FILE, JSON.stringify(db, null, 2), 'utf8');
  } catch (err) {
    console.error('Error saving database:', err);
  }
}

export function resetDatabase() {
  db = JSON.parse(JSON.stringify(DEFAULT_DATA));
  saveDatabase();
  return db;
}

export function exportDatabaseJson() {
  return JSON.stringify(getDatabase(), null, 2);
}

export function importDatabaseJson(jsonString) {
  try {
    const parsed = JSON.parse(jsonString);
    if (!parsed.categories || !parsed.runners || !parsed.registrations) {
      throw new Error('Invalid database JSON structure');
    }
    db = parsed;
    saveDatabase();
    return { success: true };
  } catch (e) {
    return { success: false, error: e.message };
  }
}

export default {
  getDatabase,
  saveDatabase,
  resetDatabase,
  exportDatabaseJson,
  importDatabaseJson
};
