import QRCode from 'qrcode';

// Calculate CRC16-CCITT for EMVCo QR code
function crc16(data) {
  let crc = 0xFFFF;
  for (let i = 0; i < data.length; i++) {
    let c = data.charCodeAt(i);
    crc ^= c << 8;
    for (let j = 0; j < 8; j++) {
      if ((crc & 0x8000) !== 0) {
        crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
      } else {
        crc = (crc << 1) & 0xFFFF;
      }
    }
  }
  let hex = (crc & 0xFFFF).toString(16).toUpperCase();
  return hex.padStart(4, '0');
}

function formatTag(id, val) {
  const strVal = String(val);
  const len = String(strVal.length).padStart(2, '0');
  return `${id}${len}${strVal}`;
}

export function generatePromptPayPayload(target, amount = null) {
  // Clean target
  const sanitized = String(target || '0812345678').replace(/[^0-9]/g, '');
  let formattedTarget = '';
  
  if (sanitized.length === 10) {
    // Phone number: convert 08x to 00668x
    formattedTarget = '0066' + sanitized.substring(1);
    formattedTarget = formatTag('01', formatTag('01', formattedTarget));
  } else if (sanitized.length === 13) {
    // Citizen ID or Tax ID
    formattedTarget = formatTag('01', formatTag('02', sanitized));
  } else {
    formattedTarget = formatTag('01', formatTag('01', '0066812345678'));
  }

  const payloadFormat = formatTag('00', '01');
  const pointOfInitiation = formatTag('01', amount ? '12' : '11');
  
  const merchantAccountInfo = formatTag('29', formatTag('00', 'A000000677010111') + formattedTarget);
  const countryCode = formatTag('58', 'TH');
  const currencyCode = formatTag('53', '764'); // THB
  
  let amountStr = '';
  if (amount && Number(amount) > 0) {
    amountStr = formatTag('54', Number(amount).toFixed(2));
  }

  const dataBeforeCRC = payloadFormat + pointOfInitiation + merchantAccountInfo + countryCode + currencyCode + amountStr + '6304';
  const checksum = crc16(dataBeforeCRC);
  
  return dataBeforeCRC + checksum;
}

export async function generateQrDataUri(text, options = {}) {
  try {
    return await QRCode.toDataURL(text, {
      errorCorrectionLevel: 'M',
      margin: 2,
      width: options.width || 280,
      color: {
        dark: options.darkColor || '#002B49',
        light: '#FFFFFF'
      }
    });
  } catch (err) {
    console.error('QR code generation error:', err);
    return '';
  }
}

export async function generatePromptPayDataUrl(target, amount = null, options = {}) {
  const payload = generatePromptPayPayload(target, amount);
  return await generateQrDataUri(payload, options);
}

export default {
  generatePromptPayPayload,
  generateQrDataUri,
  generatePromptPayDataUrl
};
