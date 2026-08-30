import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';
import cookieParser from 'cookie-parser';
import {
  getDatabase,
  saveDatabase,
  resetDatabase,
  exportDatabaseJson,
  importDatabaseJson
} from './lib/db.js';
import { generatePromptPayPayload, generateQrDataUri } from './lib/promptpay.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;

// Setup View Engine & Middleware
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));
app.use('/assets', express.static(path.join(__dirname, 'public/assets')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));

// Load database
const db = getDatabase();

// -------------------------------------------------------------
// Helper Query Functions
// -------------------------------------------------------------
function getCategory(id) {
  return db.categories.find(c => c.category_id == id);
}

function getRunner(id) {
  return db.runners.find(r => r.runner_id == id);
}

function getPriceRate(id) {
  return db.price_rates.find(p => p.price_id == id);
}

function getShipping(id) {
  return db.shipping_options.find(s => s.shipping_id == id);
}

function getRegistrationDetails(reg) {
  if (!reg) return null;
  const runner = getRunner(reg.runner_id) || {};
  const category = getCategory(reg.category_id) || {};
  const price = getPriceRate(reg.price_id) || { amount: 0, runner_type: 'Standard' };
  const shipping = getShipping(reg.shipping_id) || { cost: 0, type: 'Pickup', detail: '' };
  const payment = db.payments.find(p => p.reg_id == reg.reg_id && p.status === 'Success') || 
                  db.payments.find(p => p.reg_id == reg.reg_id) || null;

  return {
    ...reg,
    first_name: runner.first_name || '',
    last_name: runner.last_name || '',
    citizen_id: runner.citizen_id || '',
    email: runner.email || '',
    phone: runner.phone || '',
    gender: runner.gender || 'Male',
    date_of_birth: runner.date_of_birth || '',
    address: runner.address || '',
    is_disabled: runner.is_disabled || 0,
    category_name: category.name || '',
    distance_km: category.distance_km || 0,
    start_time: category.start_time || '06:00:00',
    time_limit: category.time_limit || '02:00:00',
    giveaway_type: category.giveaway_type || 'เสื้อ + เหรียญ',
    runner_type: price.runner_type || 'Standard',
    amount: Number(price.amount || 0),
    shipping_type: shipping.type || '',
    shipping_cost: Number(shipping.cost || 0),
    shipping_detail: shipping.detail || '',
    total_amount: Number(price.amount || 0) + Number(shipping.cost || 0),
    payment: payment,
    payment_status: payment ? payment.status : 'None',
    checkin_status: reg.checkin_status || 0,
    checkin_time: reg.checkin_time || null
  };
}

// Generate Category-based BIB (e.g., M001, H001, F001)
function generateNextBibNumber(categoryId) {
  const cat = getCategory(categoryId);
  let prefix = 'M';
  if (cat) {
    const km = Number(cat.distance_km);
    if (km >= 40) prefix = 'F';
    else if (km >= 20) prefix = 'H';
    else if (km >= 10) prefix = 'M';
    else prefix = 'R';
  }

  const existingBibs = db.registrations
    .filter(r => r.category_id == categoryId && r.bib_number && r.bib_number.startsWith(prefix))
    .map(r => {
      const num = parseInt(r.bib_number.replace(/\D/g, ''), 10);
      return isNaN(num) ? 0 : num;
    });

  const nextNum = existingBibs.length > 0 ? Math.max(...existingBibs) + 1 : 1;
  return `${prefix}${String(nextNum).padStart(3, '0')}`;
}

// Determine best price rate for runner
function calculatePriceRate(categoryId, runner) {
  const rates = db.price_rates.filter(p => p.category_id == categoryId);
  if (rates.length === 0) return { price_id: 1, amount: 500, runner_type: 'Standard' };

  if (runner) {
    if (runner.is_disabled) {
      const disabledRate = rates.find(r => r.runner_type === 'Disabled');
      if (disabledRate) return disabledRate;
    }
    if (runner.date_of_birth) {
      const birthYear = new Date(runner.date_of_birth).getFullYear();
      const age = 2026 - birthYear;
      if (age >= 70) {
        const seniorRate = rates.find(r => r.runner_type === 'Senior 70+' || r.runner_type.includes('70'));
        if (seniorRate) return seniorRate;
      }
      if (age < 23) {
        const studentRate = rates.find(r => r.runner_type === 'Student');
        if (studentRate) return studentRate;
      }
    }
  }

  const standardRate = rates.find(r => r.runner_type === 'Standard');
  return standardRate || rates[0];
}

// -------------------------------------------------------------
// PUBLIC ROUTES
// -------------------------------------------------------------

// Home Page
app.get(['/', '/index.php', '/index.html'], (req, res) => {
  const runnerCount = db.runners.length;
  const regCount = db.registrations.length;
  const paidCount = db.registrations.filter(r => r.status === 'Paid').length;
  
  res.render('index', {
    categories: db.categories,
    settings: db.settings,
    stats: { runnerCount, regCount, paidCount }
  });
});

// Public Check Status & E-Ticket Search
app.get(['/check_status.php', '/check_status', '/search'], (req, res) => {
  const query = (req.query.q || '').trim();
  let searchResult = null;
  let matches = [];

  if (query) {
    matches = db.registrations
      .map(getRegistrationDetails)
      .filter(r => {
        const citizen = (r.citizen_id || '').replace(/\D/g, '');
        const phone = (r.phone || '').replace(/\D/g, '');
        const queryClean = query.replace(/\D/g, '');
        const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
        const bib = (r.bib_number || '').toLowerCase();
        const regId = String(r.reg_id);

        return (
          (queryClean.length > 3 && (citizen.includes(queryClean) || phone.includes(queryClean))) ||
          fullName.includes(query.toLowerCase()) ||
          bib === query.toLowerCase() ||
          regId === query
        );
      });
  }

  res.render('check_status', {
    query,
    matches,
    settings: db.settings
  });
});

// View Printable E-Ticket / Digital Race Pass
app.get(['/e-ticket/:id', '/print_ticket/:id'], async (req, res) => {
  const reg = db.registrations.find(r => r.reg_id == req.params.id);
  if (!reg) {
    return res.status(404).send('ไม่พบข้อมูลการสมัคร');
  }

  const details = getRegistrationDetails(reg);
  const qrData = `RUN2026-REG:${details.reg_id}-BIB:${details.bib_number || 'PENDING'}-${details.citizen_id}`;
  const qrImage = await generateQrDataUri(qrData);

  res.render('e_ticket', {
    runner: details,
    qrImage,
    settings: db.settings
  });
});

// Registration Form (Public)
app.get(['/registration_form.php', '/registration_form'], (req, res) => {
  const selectedCat = req.query.cat ? Number(req.query.cat) : null;
  const recent = [...db.registrations].reverse().slice(0, 10).map(getRegistrationDetails);
  
  res.render('registration_form', {
    runners: db.runners,
    categories: db.categories,
    shipping_options: db.shipping_options,
    price_rates: db.price_rates,
    selectedCat,
    recentRegistrations: recent,
    settings: db.settings
  });
});

// Payment Form (Public)
app.get(['/payment_form.php', '/payment_form'], async (req, res) => {
  const selectedRegId = req.query.reg_id ? Number(req.query.reg_id) : null;
  const pendingRegistrations = db.registrations
    .filter(r => r.status === 'Pending')
    .map(getRegistrationDetails);

  // If a registration is selected, prepare PromptPay QR
  let selectedReg = null;
  let promptPayQr = null;
  if (selectedRegId) {
    selectedReg = getRegistrationDetails(db.registrations.find(r => r.reg_id === selectedRegId));
    if (selectedReg) {
      const payload = generatePromptPayPayload(db.settings.promptpay_number, selectedReg.total_amount);
      promptPayQr = await generateQrDataUri(payload);
    }
  }

  res.render('payment_form', {
    pendingRegistrations,
    selectedRegId,
    selectedReg,
    promptPayQr,
    settings: db.settings
  });
});

// Dynamic PromptPay QR Code API
app.get('/api/promptpay-qr', async (req, res) => {
  const amount = req.query.amount ? Number(req.query.amount) : 0;
  const target = req.query.target || db.settings.promptpay_number;
  const payload = generatePromptPayPayload(target, amount);
  const qrImage = await generateQrDataUri(payload);
  res.json({
    success: true,
    target,
    amount,
    payload,
    qrImage
  });
});

// Standalone Forms
app.get(['/runner_form.php', '/runner_form'], (req, res) => {
  const sortedRunners = [...db.runners].reverse().slice(0, 15);
  res.render('runner_form', { runners: sortedRunners });
});

app.get(['/race_category_form.php', '/race_category_form'], (req, res) => {
  res.render('race_category_form', { categories: db.categories });
});

app.get(['/age_group_form.php', '/age_group_form'], (req, res) => {
  res.render('age_group_form', { categories: db.categories, age_groups: db.age_groups });
});

app.get(['/price_rate_form.php', '/price_rate_form'], (req, res) => {
  res.render('price_rate_form', { categories: db.categories, price_rates: db.price_rates });
});

app.get(['/shipping_option_form.php', '/shipping_option_form'], (req, res) => {
  res.render('shipping_option_form', { shipping_options: db.shipping_options });
});

app.get(['/example_with_popups.php', '/example_with_popups'], (req, res) => {
  res.render('example_with_popups');
});

// -------------------------------------------------------------
// ADMIN ROUTES & CONTROLLERS
// -------------------------------------------------------------

// Admin Dashboard
app.get(['/admin_index.php', '/admin', '/admin_index'], (req, res) => {
  const runnerCount = db.runners.length;
  const regCount = db.registrations.length;
  const paidCount = db.registrations.filter(r => r.status === 'Paid').length;
  const pendingCount = db.registrations.filter(r => r.status === 'Pending').length;
  const checkedInCount = db.registrations.filter(r => r.checkin_status === 1).length;
  
  const totalRevenue = db.registrations
    .filter(r => r.status === 'Paid')
    .reduce((sum, r) => {
      const price = getPriceRate(r.price_id);
      const ship = getShipping(r.shipping_id);
      return sum + (price ? Number(price.amount) : 0) + (ship ? Number(ship.cost) : 0);
    }, 0);

  const categoryCounts = db.categories.map(cat => {
    const total = db.registrations.filter(r => r.category_id == cat.category_id).length;
    const paid = db.registrations.filter(r => r.category_id == cat.category_id && r.status === 'Paid').length;
    return { name: cat.name, total, paid, distance_km: cat.distance_km };
  });

  const recentRegistrations = [...db.registrations]
    .reverse()
    .slice(0, 8)
    .map(getRegistrationDetails);

  res.render('admin_index', {
    runnerCount,
    regCount,
    paidCount,
    pendingCount,
    checkedInCount,
    totalRevenue,
    categoryCounts,
    recentRegistrations,
    categoriesCount: db.categories.length,
    ageGroupsCount: db.age_groups.length,
    pricesCount: db.price_rates.length,
    shippingsCount: db.shipping_options.length,
    settings: db.settings
  });
});

// Manage Runners
app.get(['/manage_runners.php', '/manage_runners'], (req, res) => {
  const sortedRunners = [...db.runners].reverse();
  res.render('manage_runners', { runners: sortedRunners });
});

// Manage Registrations
app.get(['/manage_registrations.php', '/manage_registrations'], (req, res) => {
  const enrichedRegs = [...db.registrations].reverse().map(getRegistrationDetails);
  res.render('manage_registrations', {
    registrations: enrichedRegs,
    categories: db.categories,
    shipping_options: db.shipping_options
  });
});

// Manage Payments
app.get(['/manage_payments.php', '/manage_payments'], (req, res) => {
  const enrichedPayments = [...db.payments].reverse().map(p => {
    const reg = db.registrations.find(r => r.reg_id == p.reg_id) || {};
    const runner = getRunner(reg.runner_id) || {};
    const cat = getCategory(reg.category_id) || {};
    return {
      ...p,
      first_name: runner.first_name || '',
      last_name: runner.last_name || '',
      phone: runner.phone || '',
      category_name: cat.name || '',
      bib_number: reg.bib_number || '-'
    };
  });

  const successRevenue = db.payments
    .filter(p => p.status === 'Success')
    .reduce((sum, p) => sum + Number(p.total_amount), 0);
  const successCount = db.payments.filter(p => p.status === 'Success').length;
  const pendingCount = db.payments.filter(p => p.status === 'Pending').length;
  const failedCount = db.payments.filter(p => p.status === 'Failed').length;

  res.render('manage_payments', {
    payments: enrichedPayments,
    successRevenue,
    successCount,
    pendingCount,
    failedCount
  });
});

// Manage Categories
app.get(['/manage_categories.php', '/manage_categories'], (req, res) => {
  const sortedCategories = [...db.categories].sort((a, b) => a.distance_km - b.distance_km);
  res.render('manage_categories', { categories: sortedCategories });
});

// Manage Age Groups
app.get(['/manage_age_groups.php', '/manage_age_groups'], (req, res) => {
  const enrichedAgeGroups = db.age_groups.map(ag => {
    const cat = getCategory(ag.category_id) || {};
    return {
      ...ag,
      category_name: cat.name || ''
    };
  });
  res.render('manage_age_groups', {
    age_groups: enrichedAgeGroups,
    categories: db.categories
  });
});

// Manage Price Rates
app.get(['/manage_price_rates.php', '/manage_price_rates'], (req, res) => {
  const enrichedPrices = db.price_rates.map(pr => {
    const cat = getCategory(pr.category_id) || {};
    return {
      ...pr,
      category_name: cat.name || ''
    };
  });
  const standardCount = db.price_rates.filter(p => p.runner_type === 'Standard').length;
  const seniorCount = db.price_rates.filter(p => p.runner_type === 'Senior 70+').length;
  const disabledCount = db.price_rates.filter(p => p.runner_type === 'Disabled').length;
  const studentCount = db.price_rates.filter(p => p.runner_type === 'Student').length;

  res.render('manage_price_rates', {
    price_rates: enrichedPrices,
    categories: db.categories,
    standardCount,
    seniorCount,
    disabledCount,
    studentCount
  });
});

// Manage Shipping Options
app.get(['/manage_shipping_options.php', '/manage_shipping_options'], (req, res) => {
  const freeCount = db.shipping_options.filter(s => Number(s.cost) === 0).length;
  const lowCount = db.shipping_options.filter(s => Number(s.cost) > 0 && Number(s.cost) <= 50).length;
  const medCount = db.shipping_options.filter(s => Number(s.cost) > 50 && Number(s.cost) <= 100).length;
  const highCount = db.shipping_options.filter(s => Number(s.cost) > 100).length;

  res.render('manage_shipping_options', {
    shipping_options: db.shipping_options,
    freeCount,
    lowCount,
    medCount,
    highCount
  });
});

// Race Day Check-in & Distribution Tool
app.get(['/admin/checkin', '/checkin.php', '/checkin'], (req, res) => {
  const enrichedRegs = db.registrations.map(getRegistrationDetails);
  const total = enrichedRegs.length;
  const checkedIn = enrichedRegs.filter(r => r.checkin_status === 1).length;
  const pending = total - checkedIn;

  res.render('checkin', {
    registrations: enrichedRegs,
    stats: { total, checkedIn, pending },
    settings: db.settings
  });
});

// Printable Race BIB Cards
app.get(['/admin/print_bibs', '/print_bibs.php', '/print_bibs'], (req, res) => {
  const catFilter = req.query.category_id ? Number(req.query.category_id) : null;
  const regIdFilter = req.query.reg_id ? Number(req.query.reg_id) : null;

  let bibRunners = db.registrations
    .filter(r => r.status === 'Paid' && r.bib_number)
    .map(getRegistrationDetails);

  if (catFilter) {
    bibRunners = bibRunners.filter(r => r.category_id === catFilter);
  }
  if (regIdFilter) {
    bibRunners = bibRunners.filter(r => r.reg_id === regIdFilter);
  }

  res.render('bib_print', {
    runners: bibRunners,
    categories: db.categories,
    selectedCat: catFilter,
    settings: db.settings
  });
});

// Database & System Management
app.get(['/admin/database', '/manage_database.php', '/manage_database'], (req, res) => {
  res.render('manage_database', {
    stats: {
      runners: db.runners.length,
      categories: db.categories.length,
      registrations: db.registrations.length,
      payments: db.payments.length,
      age_groups: db.age_groups.length,
      price_rates: db.price_rates.length,
      shipping_options: db.shipping_options.length
    },
    settings: db.settings
  });
});

// -------------------------------------------------------------
// FAST 1-STEP & FORM SAVE HANDLERS
// -------------------------------------------------------------

// Fast 1-Step Unified Registration (Public)
app.post(['/api/register-fast', '/save_unified_registration.php'], (req, res) => {
  try {
    const {
      first_name, last_name, date_of_birth, gender, citizen_id, phone, email, address, is_disabled,
      category_id, shirt_size, shipping_id, runner_id
    } = req.body;

    let targetRunnerId = null;

    if (runner_id && Number(runner_id) > 0) {
      targetRunnerId = Number(runner_id);
    } else {
      // Check existing citizen ID
      const existingRunner = db.runners.find(r => r.citizen_id === citizen_id);
      if (existingRunner) {
        targetRunnerId = existingRunner.runner_id;
      } else {
        const newRunner = {
          runner_id: db.sequences.runner_id++,
          first_name,
          last_name,
          date_of_birth,
          gender: gender || 'Male',
          citizen_id,
          phone,
          email,
          address: address || '',
          is_disabled: is_disabled ? 1 : 0,
          created_at: new Date().toISOString()
        };
        db.runners.push(newRunner);
        targetRunnerId = newRunner.runner_id;
      }
    }

    const runner = getRunner(targetRunnerId);
    const priceRate = calculatePriceRate(Number(category_id), runner);

    const newReg = {
      reg_id: db.sequences.reg_id++,
      runner_id: targetRunnerId,
      category_id: Number(category_id),
      price_id: priceRate.price_id,
      shipping_id: Number(shipping_id),
      reg_date: new Date().toISOString().split('T')[0],
      shirt_size: shirt_size || 'L',
      status: 'Pending',
      bib_number: null,
      checkin_status: 0,
      checkin_time: null,
      created_at: new Date().toISOString()
    };

    db.registrations.push(newReg);
    saveDatabase();

    if (req.xhr || req.headers.accept?.includes('json')) {
      return res.json({
        success: true,
        message: 'สมัครวิ่งเรียบร้อยแล้ว!',
        reg_id: newReg.reg_id,
        redirectUrl: `/payment_form.php?reg_id=${newReg.reg_id}`
      });
    }

    res.redirect(`/payment_form.php?reg_id=${newReg.reg_id}`);
  } catch (err) {
    console.error('Unified registration error:', err);
    res.status(500).json({ success: false, message: 'เกิดข้อผิดพลาดในการลงทะเบียน: ' + err.message });
  }
});

// Save Runner Form
app.post(['/save_runner.php', '/save_runner'], (req, res) => {
  const { first_name, last_name, date_of_birth, gender, email, citizen_id, phone, address, is_disabled } = req.body;
  const newRunner = {
    runner_id: db.sequences.runner_id++,
    first_name,
    last_name,
    date_of_birth,
    gender,
    citizen_id,
    phone,
    email,
    address: address || '',
    is_disabled: is_disabled ? 1 : 0,
    created_at: new Date().toISOString()
  };
  db.runners.push(newRunner);
  saveDatabase();

  if (req.xhr || req.headers.accept?.includes('json')) {
    return res.json({ success: true, message: 'บันทึกข้อมูลนักวิ่งสำเร็จ!', runner: newRunner });
  }
  res.redirect('/registration_form.php');
});

// Save Registration Form
app.post(['/save_registration.php', '/save_registration'], (req, res) => {
  const { runner_id, category_id, shipping_id, shirt_size } = req.body;
  const runner = getRunner(runner_id);
  const priceRate = calculatePriceRate(Number(category_id), runner);
  
  const newReg = {
    reg_id: db.sequences.reg_id++,
    runner_id: Number(runner_id),
    category_id: Number(category_id),
    price_id: priceRate.price_id,
    shipping_id: Number(shipping_id),
    reg_date: new Date().toISOString().split('T')[0],
    shirt_size: shirt_size || 'L',
    status: 'Pending',
    bib_number: null,
    checkin_status: 0,
    checkin_time: null,
    created_at: new Date().toISOString()
  };

  db.registrations.push(newReg);
  saveDatabase();

  if (req.xhr || req.headers.accept?.includes('json')) {
    return res.json({ success: true, message: 'ลงสมัครสำเร็จ!', reg_id: newReg.reg_id });
  }
  res.redirect(`/payment_form.php?reg_id=${newReg.reg_id}`);
});

// Save Payment Form
app.post(['/save_payment.php', '/save_payment'], (req, res) => {
  const { reg_id, total_amount, payment_time, payment_method, transaction_ref, slip_url } = req.body;
  
  const newPayment = {
    payment_id: db.sequences.payment_id++,
    reg_id: Number(reg_id),
    total_amount: Number(total_amount),
    payment_time: payment_time || new Date().toISOString().replace('T', ' ').slice(0, 19),
    payment_method: payment_method || 'QR Code',
    status: 'Pending',
    transaction_ref: transaction_ref || 'TXN-' + Date.now().toString().slice(-6),
    slip_url: slip_url || null,
    created_at: new Date().toISOString()
  };

  db.payments.push(newPayment);
  saveDatabase();

  if (req.xhr || req.headers.accept?.includes('json')) {
    return res.json({ success: true, message: 'แจ้งชำระเงินสำเร็จ! เจ้าหน้าที่จะตรวจสอบยอดเงิน', payment_id: newPayment.payment_id });
  }
  res.redirect('/check_status.php?q=' + reg_id);
});

// Save Category Form
app.post(['/save_category.php', '/save_category'], (req, res) => {
  const { name, distance_km, start_time, time_limit, giveaway_type } = req.body;
  db.categories.push({
    category_id: db.sequences.category_id++,
    name,
    distance_km: Number(distance_km),
    start_time: start_time ? (start_time.includes(':') && start_time.split(':').length === 2 ? start_time + ':00' : start_time) : '06:00:00',
    time_limit: time_limit ? (time_limit.includes(':') && time_limit.split(':').length === 2 ? time_limit + ':00' : time_limit) : '02:00:00',
    giveaway_type,
    created_at: new Date().toISOString()
  });
  saveDatabase();
  res.redirect('/manage_categories.php');
});

// Save Age Group Form
app.post(['/save_age_group.php', '/save_age_group'], (req, res) => {
  const { category_id, gender, min_age, max_age, label } = req.body;
  db.age_groups.push({
    age_group_id: db.sequences.age_group_id++,
    category_id: Number(category_id),
    gender,
    min_age: Number(min_age),
    max_age: Number(max_age),
    label,
    created_at: new Date().toISOString()
  });
  saveDatabase();
  res.redirect('/manage_age_groups.php');
});

// Save Price Rate Form
app.post(['/save_price_rate.php', '/save_price_rate'], (req, res) => {
  const { category_id, runner_type, amount } = req.body;
  db.price_rates.push({
    price_id: db.sequences.price_id++,
    category_id: Number(category_id),
    runner_type,
    amount: Number(amount),
    created_at: new Date().toISOString()
  });
  saveDatabase();
  res.redirect('/manage_price_rates.php');
});

// Save Shipping Form
app.post(['/save_shipping.php', '/save_shipping'], (req, res) => {
  const { type, cost, detail } = req.body;
  db.shipping_options.push({
    shipping_id: db.sequences.shipping_id++,
    type,
    cost: Number(cost),
    detail: detail || '',
    created_at: new Date().toISOString()
  });
  saveDatabase();
  res.redirect('/manage_shipping_options.php');
});

// -------------------------------------------------------------
// AJAX CRUD ENDPOINTS
// -------------------------------------------------------------

// CRUD Runner
app.all(['/crud_runner.php', '/api/runners'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const runner = getRunner(id);
    if (runner) return res.json({ success: true, data: runner });
    return res.json({ success: false, message: 'ไม่พบข้อมูลนักวิ่ง' });
  }

  if (req.method === 'POST') {
    if (action === 'create') {
      const newRunner = {
        runner_id: db.sequences.runner_id++,
        first_name: req.body.first_name,
        last_name: req.body.last_name,
        date_of_birth: req.body.date_of_birth,
        gender: req.body.gender,
        citizen_id: req.body.citizen_id,
        phone: req.body.phone,
        email: req.body.email,
        address: req.body.address || '',
        is_disabled: req.body.is_disabled === 'on' || req.body.is_disabled === '1' || req.body.is_disabled === true ? 1 : 0,
        created_at: new Date().toISOString()
      };
      db.runners.push(newRunner);
      saveDatabase();
      if (req.xhr || req.headers.accept?.includes('json')) {
        return res.json({ success: true, message: 'เพิ่มข้อมูลนักวิ่งสำเร็จ', data: newRunner });
      }
      return res.redirect('/manage_runners.php');
    }

    if (action === 'update') {
      const idx = db.runners.findIndex(r => r.runner_id == req.body.runner_id);
      if (idx !== -1) {
        db.runners[idx] = {
          ...db.runners[idx],
          first_name: req.body.first_name,
          last_name: req.body.last_name,
          date_of_birth: req.body.date_of_birth,
          gender: req.body.gender,
          citizen_id: req.body.citizen_id,
          phone: req.body.phone,
          email: req.body.email,
          address: req.body.address || '',
          is_disabled: req.body.is_disabled === 'on' || req.body.is_disabled === '1' || req.body.is_disabled === true ? 1 : 0
        };
        saveDatabase();
        if (req.xhr || req.headers.accept?.includes('json')) {
          return res.json({ success: true, message: 'แก้ไขข้อมูลนักวิ่งสำเร็จ' });
        }
        return res.redirect('/manage_runners.php');
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลนักวิ่ง' });
    }

    if (action === 'delete') {
      const id = req.body.runner_id;
      const hasReg = db.registrations.some(r => r.runner_id == id);
      if (hasReg) {
        return res.json({ success: false, message: 'ไม่สามารถลบได้เนื่องจากนักวิ่งมีประวัติการสมัครแล้ว' });
      }
      db.runners = db.runners.filter(r => r.runner_id != id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบข้อมูลนักวิ่งสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// CRUD Registration
app.all(['/crud_registration.php', '/api/registrations'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const reg = db.registrations.find(r => r.reg_id == id);
    if (reg) return res.json({ success: true, data: reg });
    return res.json({ success: false, message: 'ไม่พบข้อมูลการสมัคร' });
  }

  if (req.method === 'POST') {
    if (action === 'update') {
      const idx = db.registrations.findIndex(r => r.reg_id == req.body.reg_id);
      if (idx !== -1) {
        db.registrations[idx].shirt_size = req.body.shirt_size || db.registrations[idx].shirt_size;
        db.registrations[idx].bib_number = req.body.bib_number !== undefined ? (req.body.bib_number.trim() || null) : db.registrations[idx].bib_number;
        db.registrations[idx].status = req.body.status || db.registrations[idx].status;
        
        // Auto assign bib if approved and empty
        if (db.registrations[idx].status === 'Paid' && !db.registrations[idx].bib_number) {
          db.registrations[idx].bib_number = generateNextBibNumber(db.registrations[idx].category_id);
        }
        
        saveDatabase();
        return res.json({ success: true, message: 'อัปเดตข้อมูลการสมัครสำเร็จ', bib_number: db.registrations[idx].bib_number });
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลการสมัคร' });
    }

    if (action === 'cancel') {
      const idx = db.registrations.findIndex(r => r.reg_id == req.body.reg_id);
      if (idx !== -1) {
        db.registrations[idx].status = 'Cancelled';
        saveDatabase();
        return res.json({ success: true, message: 'ยกเลิกการสมัครสำเร็จ' });
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลการสมัคร' });
    }

    if (action === 'auto_generate_bibs') {
      let count = 0;
      db.registrations.forEach(r => {
        if (r.status === 'Paid' && !r.bib_number) {
          r.bib_number = generateNextBibNumber(r.category_id);
          count++;
        }
      });
      saveDatabase();
      return res.json({ success: true, message: `ออกหมายเลข BIB ให้อัตโนมัติ ${count} รายการเรียบร้อยแล้ว` });
    }

    if (action === 'delete') {
      const id = req.body.reg_id;
      const hasPaid = db.payments.some(p => p.reg_id == id && p.status === 'Success');
      if (hasPaid) {
        return res.json({ success: false, message: 'ไม่สามารถลบได้ เนื่องจากมีการชำระเงินแล้ว' });
      }
      db.registrations = db.registrations.filter(r => r.reg_id != id);
      db.payments = db.payments.filter(p => p.reg_id != id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบข้อมูลสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// CRUD Payment
app.all(['/crud_payment.php', '/api/payments'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const payment = db.payments.find(p => p.payment_id == id);
    if (payment) return res.json({ success: true, data: payment });
    return res.json({ success: false, message: 'ไม่พบข้อมูลการชำระเงิน' });
  }

  if (req.method === 'POST') {
    if (action === 'update') {
      const idx = db.payments.findIndex(p => p.payment_id == req.body.payment_id);
      if (idx !== -1) {
        db.payments[idx].total_amount = Number(req.body.total_amount);
        db.payments[idx].payment_method = req.body.payment_method;
        db.payments[idx].status = req.body.status;
        db.payments[idx].transaction_ref = req.body.transaction_ref || null;
        
        // Auto update registration status & BIB
        const regIdx = db.registrations.findIndex(r => r.reg_id == db.payments[idx].reg_id);
        if (regIdx !== -1) {
          if (req.body.status === 'Success') {
            db.registrations[regIdx].status = 'Paid';
            if (!db.registrations[regIdx].bib_number) {
              db.registrations[regIdx].bib_number = generateNextBibNumber(db.registrations[regIdx].category_id);
            }
          } else if (req.body.status === 'Failed') {
            db.registrations[regIdx].status = 'Pending';
          }
        }
        saveDatabase();
        return res.json({ success: true, message: 'อัปเดตการชำระเงินสำเร็จ' });
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูล' });
    }

    if (action === 'update_status') {
      const idx = db.payments.findIndex(p => p.payment_id == req.body.payment_id);
      if (idx !== -1) {
        db.payments[idx].status = req.body.status;
        const regIdx = db.registrations.findIndex(r => r.reg_id == db.payments[idx].reg_id);
        if (regIdx !== -1) {
          if (req.body.status === 'Success') {
            db.registrations[regIdx].status = 'Paid';
            if (!db.registrations[regIdx].bib_number) {
              db.registrations[regIdx].bib_number = generateNextBibNumber(db.registrations[regIdx].category_id);
            }
          } else if (req.body.status === 'Failed') {
            db.registrations[regIdx].status = 'Pending';
          }
        }
        saveDatabase();
        return res.json({ success: true, message: 'ปรับสถานะเรียบร้อย' });
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูล' });
    }

    if (action === 'delete') {
      db.payments = db.payments.filter(p => p.payment_id != req.body.payment_id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบการชำระเงินสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// Race Day Check-in Action
app.post('/api/checkin', (req, res) => {
  const { reg_id, checked_by, note } = req.body;
  const regIdx = db.registrations.findIndex(r => r.reg_id == reg_id);
  if (regIdx === -1) {
    return res.json({ success: false, message: 'ไม่พบข้อมูลการสมัคร' });
  }

  const isCheckedIn = db.registrations[regIdx].checkin_status === 1;
  const newStatus = isCheckedIn ? 0 : 1;
  const now = new Date().toISOString().replace('T', ' ').slice(0, 19);

  db.registrations[regIdx].checkin_status = newStatus;
  db.registrations[regIdx].checkin_time = newStatus ? now : null;

  if (newStatus) {
    db.checkins.push({
      checkin_id: db.sequences.checkin_id++,
      reg_id: Number(reg_id),
      checked_by: checked_by || 'เจ้าหน้าที่',
      checkin_time: now,
      note: note || 'รับเบอร์วิ่งและเสื้อแล้ว'
    });
  }

  saveDatabase();
  res.json({
    success: true,
    checkin_status: newStatus,
    checkin_time: db.registrations[regIdx].checkin_time,
    message: newStatus ? 'เช็คอินและบันทึกการรับเบอร์วิ่งสำเร็จ' : 'ยกเลิกสถานะการเช็คอินเรียบร้อย'
  });
});

// CRUD Category
app.all(['/crud_category.php', '/api/categories'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const cat = getCategory(id);
    if (cat) return res.json({ success: true, data: cat });
    return res.json({ success: false, message: 'ไม่พบข้อมูลประเภท' });
  }

  if (req.method === 'POST') {
    if (action === 'create') {
      db.categories.push({
        category_id: db.sequences.category_id++,
        name: req.body.name,
        distance_km: Number(req.body.distance_km),
        start_time: req.body.start_time ? (req.body.start_time.includes(':') && req.body.start_time.split(':').length === 2 ? req.body.start_time + ':00' : req.body.start_time) : '06:00:00',
        time_limit: req.body.time_limit ? (req.body.time_limit.includes(':') && req.body.time_limit.split(':').length === 2 ? req.body.time_limit + ':00' : req.body.time_limit) : '02:00:00',
        giveaway_type: req.body.giveaway_type,
        created_at: new Date().toISOString()
      });
      saveDatabase();
      return res.redirect('/manage_categories.php');
    }

    if (action === 'update') {
      const idx = db.categories.findIndex(c => c.category_id == req.body.category_id);
      if (idx !== -1) {
        db.categories[idx] = {
          ...db.categories[idx],
          name: req.body.name,
          distance_km: Number(req.body.distance_km),
          start_time: req.body.start_time.includes(':') && req.body.start_time.split(':').length === 2 ? req.body.start_time + ':00' : req.body.start_time,
          time_limit: req.body.time_limit.includes(':') && req.body.time_limit.split(':').length === 2 ? req.body.time_limit + ':00' : req.body.time_limit,
          giveaway_type: req.body.giveaway_type
        };
        saveDatabase();
        return res.redirect('/manage_categories.php');
      }
      return res.json({ success: false, message: 'ไม่พบประเภท' });
    }

    if (action === 'delete') {
      const id = req.body.category_id;
      const inUse = db.registrations.some(r => r.category_id == id);
      if (inUse) {
        return res.json({ success: false, message: 'ไม่สามารถลบได้เนื่องจากมีผู้สมัครในประเภทนี้แล้ว' });
      }
      db.categories = db.categories.filter(c => c.category_id != id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบประเภทการแข่งขันสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// CRUD Age Group
app.all(['/crud_age_group.php', '/api/age-groups'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const ag = db.age_groups.find(a => a.age_group_id == id);
    if (ag) return res.json({ success: true, data: ag });
    return res.json({ success: false, message: 'ไม่พบข้อมูลกลุ่มอายุ' });
  }

  if (req.method === 'POST') {
    if (action === 'create') {
      db.age_groups.push({
        age_group_id: db.sequences.age_group_id++,
        category_id: Number(req.body.category_id),
        gender: req.body.gender,
        min_age: Number(req.body.min_age),
        max_age: Number(req.body.max_age),
        label: req.body.label,
        created_at: new Date().toISOString()
      });
      saveDatabase();
      return res.redirect('/manage_age_groups.php');
    }

    if (action === 'update') {
      const idx = db.age_groups.findIndex(a => a.age_group_id == req.body.age_group_id);
      if (idx !== -1) {
        db.age_groups[idx] = {
          ...db.age_groups[idx],
          category_id: Number(req.body.category_id),
          gender: req.body.gender,
          min_age: Number(req.body.min_age),
          max_age: Number(req.body.max_age),
          label: req.body.label
        };
        saveDatabase();
        return res.redirect('/manage_age_groups.php');
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลกลุ่มอายุ' });
    }

    if (action === 'delete') {
      db.age_groups = db.age_groups.filter(a => a.age_group_id != req.body.age_group_id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบกลุ่มอายุสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// CRUD Price Rate
app.all(['/crud_price_rate.php', '/api/price-rates'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const pr = db.price_rates.find(p => p.price_id == id);
    if (pr) return res.json({ success: true, data: pr });
    return res.json({ success: false, message: 'ไม่พบข้อมูลราคา' });
  }

  if (req.method === 'POST') {
    if (action === 'create') {
      db.price_rates.push({
        price_id: db.sequences.price_id++,
        category_id: Number(req.body.category_id),
        runner_type: req.body.runner_type,
        amount: Number(req.body.amount),
        created_at: new Date().toISOString()
      });
      saveDatabase();
      return res.redirect('/manage_price_rates.php');
    }

    if (action === 'update') {
      const idx = db.price_rates.findIndex(p => p.price_id == req.body.price_id);
      if (idx !== -1) {
        db.price_rates[idx] = {
          ...db.price_rates[idx],
          category_id: Number(req.body.category_id),
          runner_type: req.body.runner_type,
          amount: Number(req.body.amount)
        };
        saveDatabase();
        return res.redirect('/manage_price_rates.php');
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลราคา' });
    }

    if (action === 'delete') {
      const id = req.body.price_id;
      const inUse = db.registrations.some(r => r.price_id == id);
      if (inUse) {
        return res.json({ success: false, message: 'ไม่สามารถลบได้เนื่องจากมีการใช้ราคานี้ในรายการสมัคร' });
      }
      db.price_rates = db.price_rates.filter(p => p.price_id != id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบอัตราค่าสมัครสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// CRUD Shipping Option
app.all(['/crud_shipping_option.php', '/api/shipping-options'], (req, res) => {
  const action = req.method === 'GET' ? req.query.action : req.body.action;

  if (action === 'get') {
    const id = req.query.id || req.body.id;
    const ship = db.shipping_options.find(s => s.shipping_id == id);
    if (ship) return res.json({ success: true, data: ship });
    return res.json({ success: false, message: 'ไม่พบข้อมูลการจัดส่ง' });
  }

  if (req.method === 'POST') {
    if (action === 'create') {
      db.shipping_options.push({
        shipping_id: db.sequences.shipping_id++,
        type: req.body.type,
        cost: Number(req.body.cost),
        detail: req.body.detail || '',
        created_at: new Date().toISOString()
      });
      saveDatabase();
      return res.redirect('/manage_shipping_options.php');
    }

    if (action === 'update') {
      const idx = db.shipping_options.findIndex(s => s.shipping_id == req.body.shipping_id);
      if (idx !== -1) {
        db.shipping_options[idx] = {
          ...db.shipping_options[idx],
          type: req.body.type,
          cost: Number(req.body.cost),
          detail: req.body.detail || ''
        };
        saveDatabase();
        return res.redirect('/manage_shipping_options.php');
      }
      return res.json({ success: false, message: 'ไม่พบข้อมูลการจัดส่ง' });
    }

    if (action === 'delete') {
      const id = req.body.shipping_id;
      const inUse = db.registrations.some(r => r.shipping_id == id);
      if (inUse) {
        return res.json({ success: false, message: 'ไม่สามารถลบได้เนื่องจากมีการใช้งานตัวเลือกนี้' });
      }
      db.shipping_options = db.shipping_options.filter(s => s.shipping_id != id);
      saveDatabase();
      return res.json({ success: true, message: 'ลบตัวเลือกการจัดส่งสำเร็จ' });
    }
  }

  res.json({ success: false, message: 'Invalid request' });
});

// -------------------------------------------------------------
// EXPORT & DATABASE TOOLS
// -------------------------------------------------------------

// Export Registrations (CSV)
app.get(['/export_registrations.php', '/export_registrations'], (req, res) => {
  res.setHeader('Content-Type', 'text/csv; charset=utf-8');
  res.setHeader('Content-Disposition', 'attachment; filename="running_registrations_2026.csv"');
  
  let csv = '\uFEFFReg ID,ชื่อ-นามสกุล,เพศ,วันเกิด,เลขบัตรประชาชน,เบอร์โทร,อีเมล,ประเภทการแข่งขัน,ระยะทาง(KM),ไซส์เสื้อ,การจัดส่ง,ค่าสมัคร,ค่าส่ง,ยอดรวม,สถานะชำระเงิน,BIB,เช็คอิน,วันที่สมัคร\n';
  db.registrations.forEach(r => {
    const d = getRegistrationDetails(r);
    csv += `"${d.reg_id}","${d.first_name} ${d.last_name}","${d.gender}","${d.date_of_birth}","${d.citizen_id}","${d.phone}","${d.email}","${d.category_name}","${d.distance_km}","${d.shirt_size}","${d.shipping_type}","${d.amount}","${d.shipping_cost}","${d.total_amount}","${d.status}","${d.bib_number || ''}","${d.checkin_status ? 'รับของแล้ว' : 'ยังไม่รับ'}","${d.reg_date}"\n`;
  });
  res.send(csv);
});

// Export Database JSON Backup
app.get('/api/database/backup', (req, res) => {
  res.setHeader('Content-Type', 'application/json');
  res.setHeader('Content-Disposition', 'attachment; filename="running_backup_' + Date.now() + '.json"');
  res.send(exportDatabaseJson());
});

// Restore Database JSON
app.post('/api/database/restore', (req, res) => {
  const jsonContent = req.body.json_data;
  if (!jsonContent) {
    return res.status(400).json({ success: false, message: 'ไม่มีข้อมูล JSON สำหรับกู้คืน' });
  }
  const result = importDatabaseJson(jsonContent);
  if (result.success) {
    res.json({ success: true, message: 'กู้คืนฐานข้อมูลสำเร็จ' });
  } else {
    res.status(400).json({ success: false, message: 'เกิดข้อผิดพลาด: ' + result.error });
  }
});

// Reset to default seed
app.post('/api/database/reset', (req, res) => {
  resetDatabase();
  res.json({ success: true, message: 'รีเซ็ตข้อมูลเริ่มต้นเรียบร้อยแล้ว' });
});

// Health check endpoint
app.get(['/api/health', '/check_errors.php'], (req, res) => {
  res.json({
    status: 'ok',
    message: 'Running 2026 Production-Ready Node.js System is online',
    timestamp: new Date().toISOString(),
    stats: {
      runners: db.runners.length,
      categories: db.categories.length,
      registrations: db.registrations.length,
      payments: db.payments.length
    }
  });
});

// 404 Fallback to Index
app.use((req, res) => {
  res.status(404).render('index', { categories: db.categories, settings: db.settings });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Running Event Management Server running on http://0.0.0.0:${PORT}`);
});
