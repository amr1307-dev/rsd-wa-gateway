# 🏛️ RED SEA DIGITAL (RSD) — ENTERPRISE AI ARCHITECTURE & CODEBASE

Welcome to the **Red Sea Digital (RSD)** engineering repository. This repository contains the complete ecosystem powering `https://redseadigital.pro/`, including the core WordPress multi-agent engine, the standalone Node.js WhatsApp gateway microservice, and system architecture dossiers.

---

## 📁 Repository Structure

```
├── redsea-ai-engine.php               # Core WordPress Enterprise AI Plugin (11,800+ lines)
├── includes/                          # Modular PHP helpers & extensions
├── assets/                            # Styles, icons, and frontend assets
├── admin/                             # Admin dashboard assets
├── RAG/                               # Vector Store context & knowledge base files
├── rsd-wa-gateway/                    # Companion Node.js Baileys Socket Gateway
│   ├── server.js                      # Express + Baileys multi-device socket server
│   ├── package.json                   # Gateway dependencies & scripts
│   ├── Dockerfile                     # Containerization specification
│   ├── docker-compose.yml             # Local / Production container deployment
│   └── .env.example                   # Example environment configuration
├── ARCHITECTURAL_HANDOVER_BRIEF.md    # Exhaustive Technical Architecture & Audit Brief
├── README.md                          # Project overview & deployment instructions
└── .gitignore                         # Strict exclusion of active sessions & secrets
```

---

## 🚀 Key Functional Systems

1. **Autonomous Outbound Lead Radar (Tab 9)**:
   - 4-Agent Pipeline: Scout Prospector ➔ Deep Analyst (OTA gap & revenue loss) ➔ Strategist (Amr Ahmed Egyptian persona) ➔ Closer.
   - Strictly enforced **Human-in-the-Loop** approval gate before any outreach message is dispatched.
   - Dedicated database tables: `wp_rsd_leads` & `wp_rsd_bookings`.

2. **WhatsApp Multi-Device Gateway (`rsd-wa-gateway`)**:
   - Zero-camera 8-digit Pairing Code (`/instance/pairingCode/:instance`) & dynamic QR generation.
   - Cryptographic token verification (`apikey` / `Bearer` headers).
   - Anti-Ban human simulation with dynamic jittered typing delays and 4-turn bot circuit breaker.

3. **Multi-Agent RAG Orchestration**:
   - Grounded vector knowledge retrieval (`wp_rsd_vector_store`).
   - Fallback model routing: OpenCode ➔ Gemini 2.5 ➔ DeepSeek.
   - Persona: **م. عمرو أحمد (Eng. Amr Ahmed)** — Founder & Chief Technology Architect.

---

## 🛠️ Quickstart & Local Setup

### 1. WordPress Plugin Setup
1. Place `redsea-ai-engine.php` (and accompanying folders) in `wp-content/plugins/redsea-ai-engine/`.
2. Activate the plugin via WP Admin ➔ Plugins.
3. Access the dashboard via **RED SEA AI Engine** in the sidebar.

### 2. Node.js Gateway Setup
```bash
cd rsd-wa-gateway
npm install
cp .env.example .env
# Edit .env with your port, API_KEY, and WordPress webhook URL
npm start
```

---

## 📖 Architectural Audit & Senior Code Review
For the full unvarnished technical audit, known bottlenecks, and specific evaluation directives for **Claude Code**, refer to:
📄 **[`ARCHITECTURAL_HANDOVER_BRIEF.md`](ARCHITECTURAL_HANDOVER_BRIEF.md)**

---

**© 2026 Red Sea Digital Agency. All Rights Reserved.**
