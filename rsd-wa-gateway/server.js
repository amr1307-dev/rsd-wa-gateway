require('dotenv').config();
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const path = require('path');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore
} = require('@whiskeysockets/baileys');

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const PORT = process.env.PORT || 3000;
const API_KEY = process.env.API_KEY || 'rsd_secret_token_2026';
const WEBHOOK_URL = process.env.WEBHOOK_URL || 'https://redseadigital.pro/wp-json/rsd/v1/whatsapp-webhook';

// Active Sessions Store
const sessions = new Map();
const qrCodes = new Map();
const pairingCodes = new Map();

// Authentication Middleware
function authMiddleware(req, res, next) {
    const key = req.headers['apikey'] || req.headers['authorization']?.replace('Bearer ', '') || req.query.apikey || req.query.token;
    if (API_KEY && key && key !== API_KEY) {
        return res.status(401).json({ error: 'Unauthorized: Invalid API Key' });
    }
    next();
}

// Healthcheck
app.get('/', (req, res) => {
    res.json({
        status: 'online',
        service: 'RED SEA DIGITAL — WhatsApp Multi-Device Gateway',
        activeSessions: Array.from(sessions.keys()),
        time: new Date().toISOString()
    });
});

// Initialize WhatsApp Socket Instance
async function initSession(instance = 'rsd_live', phoneNumber = null) {
    const authPath = path.resolve(`./sessions/${instance}`);

    // If already connected and not requesting re-pairing
    if (sessions.has(instance) && sessions.get(instance)?.user && !phoneNumber) {
        return { status: 'already_connected', instance };
    }

    // Ensure session directory exists
    fs.mkdirSync(authPath, { recursive: true });

    const { state, saveCreds } = await useMultiFileAuthState(authPath);
    const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

    const sock = makeWASocket({
        version,
        logger: pino({ level: 'silent' }),
        printQRInTerminal: false,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' }))
        },
        browser: ['RED SEA DIGITAL AI', 'Chrome', '120.0.0']
    });

    sessions.set(instance, sock);

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            const qrBase64 = await qrcode.toDataURL(qr);
            qrCodes.set(instance, qrBase64);
        }

        if (connection === 'close') {
            const statusCode = (lastDisconnect?.error)?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            qrCodes.delete(instance);
            pairingCodes.delete(instance);
            if (shouldReconnect) {
                initSession(instance, phoneNumber);
            } else {
                sessions.delete(instance);
                if (fs.existsSync(authPath)) {
                    try { fs.rmSync(authPath, { recursive: true, force: true }); } catch (e) {}
                }
            }
        } else if (connection === 'open') {
            console.log(`[RSD WA Gateway] Instance [${instance}] Connected Successfully as ${sock.user?.id}`);
            qrCodes.delete(instance);
            pairingCodes.delete(instance);
        }
    });

    // Inbound Messages Forwarder to WordPress Webhook
    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type !== 'notify') return;

        for (const msg of messages) {
            if (!msg.message || msg.key.fromMe) continue;

            const sender = msg.key.remoteJid;
            if (sender.includes('@g.us')) continue; // Ignore groups

            const text = msg.message.conversation || msg.message.extendedTextMessage?.text || '';
            if (!text.trim()) continue;

            try {
                // Forward payload to WordPress Webhook
                await fetch(WEBHOOK_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'apikey': API_KEY,
                        'Authorization': `Bearer ${API_KEY}`
                    },
                    body: JSON.stringify({
                        data: {
                            key: {
                                remoteJid: sender,
                                fromMe: false,
                                id: msg.key.id
                            },
                            pushName: msg.pushName || 'عميل واتساب',
                            message: {
                                conversation: text
                            }
                        }
                    })
                });
            } catch (err) {
                console.error('[RSD Webhook Dispatch Error]', err.message);
            }
        }
    });

    // Request 8-Digit Pairing Code if Phone Number is provided
    if (phoneNumber && !sock.authState?.creds?.registered) {
        setTimeout(async () => {
            try {
                const cleanPhone = phoneNumber.replace(/[^0-9]/g, '');
                const code = await sock.requestPairingCode(cleanPhone);
                pairingCodes.set(instance, code);
                console.log(`[RSD WA Gateway] Generated Pairing Code for ${cleanPhone}: ${code}`);
            } catch (err) {
                console.error('[Pairing Code Request Error]', err.message);
            }
        }, 2000);
    }

    return { status: 'initializing', instance };
}

// 1. Connect / Get QR or Pairing Code Endpoint (Supports GET & POST)
app.all(['/instance/connect/:instance', '/instance/pairingCode/:instance'], authMiddleware, async (req, res) => {
    const instance = req.params.instance || 'rsd_live';
    const phoneNumber = req.query.number || req.body?.number || req.query.phone || req.body?.phone || null;

    const existing = sessions.get(instance);
    if (existing && existing.user && !phoneNumber) {
        return res.json({
            status: 'connected',
            instance: { state: 'open', user: existing.user }
        });
    }

    await initSession(instance, phoneNumber);

    // If phone number is passed, wait for pairing code up to 6 seconds
    if (phoneNumber) {
        for (let i = 0; i < 10; i++) {
            const code = pairingCodes.get(instance);
            if (code) {
                return res.json({
                    status: 'code_generated',
                    pairingCode: code,
                    code: code,
                    phone: phoneNumber,
                    instance
                });
            }
            await new Promise(r => setTimeout(r, 600));
        }
    }

    // Return Base64 QR Code if available
    const base64QR = qrCodes.get(instance);
    if (base64QR) {
        return res.json({
            status: 'qr_generated',
            base64: base64QR,
            qrcode: { base64: base64QR },
            instance
        });
    }

    res.json({
        status: 'initializing',
        message: 'الجلسة قيد التهيئة وتوليد الرمز...',
        instance
    });
});

// 2. Outbound Send Message Endpoint
app.post('/message/sendText/:instance', authMiddleware, async (req, res) => {
    const instance = req.params.instance || 'rsd_live';
    const { number, text, options } = req.body;

    if (!sessions.has(instance) || !sessions.get(instance).user) {
        return res.status(503).json({ error: 'Instance not connected to WhatsApp' });
    }

    const sock = sessions.get(instance);
    const cleanPhone = number.replace(/[^0-9]/g, '');
    const jid = `${cleanPhone}@s.whatsapp.net`;

    const delay = options?.delay || 2000;

    // Human typing simulation
    await sock.sendPresenceUpdate('composing', jid);

    setTimeout(async () => {
        try {
            await sock.sendMessage(jid, { text });
            await sock.sendPresenceUpdate('paused', jid);
            res.json({ status: 'sent', to: cleanPhone, text });
        } catch (err) {
            res.status(500).json({ error: 'Failed to send message: ' + err.message });
        }
    }, delay);
});

// 3. Unified Instance Status Endpoint (Supports /instance/status and /instance/connectionState)
app.all(['/instance/status/:instance', '/instance/connectionState/:instance'], authMiddleware, (req, res) => {
    const instance = req.params.instance || 'rsd_live';
    const sock = sessions.get(instance);
    const isConnected = !!(sock && sock.user);

    res.json({
        instance,
        state: isConnected ? 'open' : 'close',
        user: isConnected ? sock.user : null
    });
});

// 4. Force Reset & Logout (Wipes stale sessions completely)
app.all(['/instance/logout/:instance', '/instance/delete/:instance'], authMiddleware, async (req, res) => {
    const instance = req.params.instance || 'rsd_live';
    const sock = sessions.get(instance);
    if (sock) {
        try { await sock.logout(); } catch (e) {}
        try { sock.ws?.close(); sock.end?.(); } catch (e) {}
    }
    sessions.delete(instance);
    qrCodes.delete(instance);
    pairingCodes.delete(instance);

    const authPath = path.resolve(`./sessions/${instance}`);
    if (fs.existsSync(authPath)) {
        try { fs.rmSync(authPath, { recursive: true, force: true }); } catch (e) {}
    }

    console.log(`[RSD WA Gateway] Instance [${instance}] Force-Reset & Logged Out.`);
    res.json({ status: 'logged_out', instance });
});

app.listen(PORT, () => {
    console.log(`====================================================`);
    console.log(`🚀 RSD WhatsApp Socket Gateway running on port ${PORT}`);
    console.log(`📡 Webhook Destination: ${WEBHOOK_URL}`);
    console.log(`🔐 API Key: ${API_KEY}`);
    console.log(`====================================================`);
});