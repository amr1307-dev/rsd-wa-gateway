# 🏛️ RED SEA DIGITAL (RSD) — SYSTEM ARCHITECTURE & TECHNICAL HANDOVER DOSSIER

**Document Classification**: Senior Architectural Handover & Raw Code Audit  
**Target Reviewer**: Claude Code (Lead AI Systems & Solutions Architect)  
**Author**: Lead Solutions Architecture Division — Red Sea Digital  
**Repository & Live Systems**:
- Core Platform: `https://redseadigital.pro/` (`redsea-ai-engine.php`)
- Companion Microservice: `https://github.com/amr1307-dev/rsd-wa-gateway` (`rsd-wa-gateway`)
- Primary Database: MariaDB / MySQL (WordPress Core Schema + RSD Custom Tables)

---

## 1. Executive Product & Architectural Summary

Red Sea Digital is an enterprise AI engineering and direct booking platform tailored for high-ticket hospitality, marine tourism, and specialized service operators in Egypt and the MENA region. 

The core commercial objective is **disintermediating Online Travel Agencies (OTAs like Booking.com and Expedia)** by reclaiming the 15%–30% gross commission leakage. It achieves this by combining:
1. Turnkey, high-conversion direct booking infrastructures.
2. An autonomous multi-agent WhatsApp sales concierge and CRM orchestrator.
3. An outbound prospective lead-generation radar with a strict **Human-in-the-Loop** approval gate.

---

## 2. Technical Stack & Functional Deep-Dive

```mermaid
flowchart TB
    subgraph ClientLayer [Client & External Touchpoints]
        WAUser[WhatsApp Guest / Prospect]
        AdminUser[Amr Ahmed / Admin Console]
        OTA[Booking.com / Expedia Ecosystem]
    end

    subgraph Microservice [Socket Gateway: rsd-wa-gateway]
        ExpressSvr[Node.js Express Server :3000]
        BaileysSock[Baileys WS Multi-Device Client]
        DiskSession[./sessions/rsd_live MultiFileAuth]
    end

    subgraph WPCore [WordPress Core: redseadigital.pro]
        RESTGate[/wp-json/rsd/v1/whatsapp-webhook]
        Orchestrator[RedSeaChiefOrchestrator]
        RAGAgent[RedSeaRAGAgent & Vector Store]
        RadarEngine[RedSeaLeadRadarEngine]
        CRMTables[(wp_rsd_leads & wp_rsd_bookings)]
    end

    subgraph LLMProviders [AI Model Gateway]
        OpenCode[OpenCode / Gemini 2.5 / DeepSeek]
    end

    WAUser <-->|Encrypted WS Stream| BaileysSock
    BaileysSock <--> ExpressSvr
    ExpressSvr -->|Inbound Webhook POST + Token| RESTGate
    RESTGate --> Orchestrator
    Orchestrator <--> RAGAgent
    Orchestrator <--> LLMProviders
    Orchestrator --> CRMTables
    RESTGate -->|Outbound Dispatch + Jitter Delay| ExpressSvr
    AdminUser <-->|Tab 9: Radar / Tab 8: CRM| WPCore
```

---

### A. WhatsApp Socket Gateway (`rsd-wa-gateway`)
- **Technology**: Node.js, Express.js, `@whiskeysockets/baileys`, `qrcode`, `pino`.
- **Session Lifecycle & Pairing Handshake**:
  - Employs `useMultiFileAuthState` caching credentials under `./sessions/{instance}`.
  - Supports dual pairing methods:
    1. Dynamic Base64 QR Code string stream.
    2. Zero-camera 8-digit Pairing Code (`sock.requestPairingCode(phone)`) for mobile-to-mobile pairing.
  - Force-reset endpoint (`/instance/logout/:instance`) cleanly closes active WebSockets, clears memory maps, and deletes `./sessions/{instance}` recursively.
- **Bi-directional Webhook Bridge**:
  - Inbound messages (`messages.upsert`, `notify`) trigger an asynchronous JSON `POST` to `https://redseadigital.pro/wp-json/rsd/v1/whatsapp-webhook`.
  - Authenticated via `apikey` / `Authorization: Bearer` headers.
- **Anti-Ban & Human Behavior Simulation**:
  - Outbound endpoint (`/message/sendText/:instance`) enforces `sock.sendPresenceUpdate('composing', jid)`.
  - Applies a dynamic delay formula: `latency = clamp(2000ms, 4500ms, 2000 + (length * 15ms) + rand(100, 400))`.

---

### B. Autonomous Outbound Lead Radar (Tab 9: `wp-admin`)
- **Multi-Agent Pipeline**:
  1. **Scout & Prospector Agent**: Probes target niches (e.g. Hurghada/Sharm resorts, diving centers, clinics) to extract metadata, domains, and phone numbers.
  2. **Deep Analyst Agent**: Evaluates website performance, mobile UX, absence of direct booking engines, and computes estimated annual OTA commission losses.
  3. **Strategist & Copy Agent**: Generates tailored outreach in **Amr Ahmed's** authentic persona (warm Egyptian dialect, value-led, non-spammy).
  4. **Human-in-the-Loop Approval Gate**: All discovered records land in `wp_rsd_leads` with `pipeline_status = 'pending_review'`. Messages are **never** dispatched without explicit admin approval via the UI.
  5. **Closer & Execution Agent**: Once approved, dispatches the pitch through `send_whatsapp_message()`, moves the status to `contacting`, and logs the customer into `wp_rsd_bookings`.

---

### C. Database Schemas

#### 1. `wp_rsd_leads` (Prospect Radar)
```sql
CREATE TABLE wp_rsd_leads (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    target_industry VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    website_url VARCHAR(255) DEFAULT '',
    gap_analysis LONGTEXT DEFAULT NULL, -- JSON: strengths, critical_gaps, revenue_loss_estimate
    tailored_pitch TEXT DEFAULT NULL,
    pipeline_status VARCHAR(50) DEFAULT 'pending_review', -- pending_review | contacting | closed | rejected
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY pipeline_status (pipeline_status)
);
```

#### 2. `wp_rsd_bookings` (CRM Leads & Conversations)
```sql
CREATE TABLE wp_rsd_bookings (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    booking_details TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);
```

#### 3. `wp_rsd_vector_store` (RAG Embeddings & Chunks)
```sql
CREATE TABLE wp_rsd_vector_store (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    doc_id VARCHAR(100) NOT NULL,
    chunk_text LONGTEXT NOT NULL,
    embedding LONGTEXT NOT NULL, -- Serialized vector array
    metadata LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);
```

---

### D. AI Reasoning & Persona Modeling
- **Orchestration Fallback Stack**: Primary LLM Provider ➔ Secondary Backup Provider (Gemini / DeepSeek / OpenCode).
- **Persona Engineering**:
  - Operates as **م. عمرو أحمد (Eng. Amr Ahmed)** — Founder & Chief Technology Architect of Red Sea Digital.
  - Tone: Quiet Luxury, authoritative yet warm Egyptian Arabic dialect, highly numeric, zero sales fluff, focused on net direct revenue retention.
- **Circuit Breaker Protocol**: If a prospect or bot exchanges more than 4 messages within a 60-second window, the system halts automated AI replies, triggers a 10-minute cooldown, and flags the record for human sales intervention.

---

## 3. Raw Engineering Audit & Known Architectural Bottlenecks

> [!WARNING]
> The following section is a brutally honest technical audit of the current implementation to guide future refactoring.

### 1. Monolithic PHP Architecture (`redsea-ai-engine.php`)
- **Current State**: Single file containing **11,800+ lines of PHP code**.
- **Defects & Risks**:
  - Violates Single Responsibility Principle (SRP). Houses REST routing, AI model connectors, Vector RAG calculations, database DDLs, and extensive inline admin HTML/CSS/JavaScript within a single file.
  - High risk of syntax regressions during hot-patching.
  - Memory consumption overhead on standard WordPress frontend requests (though partially mitigated by admin-check hooks).
- **Required Remedy**: Modularize into a clean PSR-4 domain structure (`/src/Core/`, `/src/Agents/`, `/src/Gateway/`, `/src/CRM/`, `/src/Admin/`).

---

### 2. Node Gateway Session Ephemerality (`rsd-wa-gateway`)
- **Current State**: Sessions are stored directly on the local filesystem (`./sessions/rsd_live`).
- **Defects & Risks**:
  - If deployed on serverless or containerized environments with ephemeral storage (e.g. basic Docker containers or free-tier platforms without persistent volumes), session tokens are destroyed upon container restarts, forcing QR/code re-pairing.
  - In-memory JavaScript `Map()` objects (`sessions`, `qrCodes`, `pairingCodes`) do not scale horizontally across multiple worker nodes or load balancers.
- **Required Remedy**: Decouple session storage to Redis or PostgreSQL using a database-backed Baileys auth adapter (`@whiskeysockets/baileys` DB Auth).

---

### 3. Synchronous Webhook Latency vs. Background Workers
- **Current State**: Inbound WhatsApp webhooks execute the entire AI RAG pipeline synchronously before returning the HTTP response.
- **Defects & Risks**:
  - LLM round-trip latency (8,000ms – 14,000ms) leaves the incoming HTTP connection open for a prolonged period.
  - Potential for gateway timeout if the LLM provider experiences latency spikes.
- **Required Remedy**: Implement an asynchronous queue (e.g. WordPress ActionScheduler, Redis BullMQ, or RabbitMQ) to immediately acknowledge incoming webhooks (`HTTP 200 { status: 'queued' }`) and process agent inference in a background worker.

---

### 4. Admin UI Architecture
- **Current State**: Rendered via PHP string concatenation and embedded inline vanilla JavaScript inside `render_crm_page()`.
- **Defects & Risks**:
  - UI state management is brittle (mixed DOM updates and AJAX fetch calls).
  - Lack of reactivity in real-time chat updates without full page reloads.
- **Required Remedy**: Migrate Tab 8 (CRM) and Tab 9 (Lead Radar) to a lightweight decoupled React/Vue or Alpine.js component architecture powered by WordPress REST API endpoints.

---

## 4. Specific Directives for Claude Code Senior Review

Claude Code is requested to perform a deep-dive review focusing on the following 3 architectural directives:

### 🎯 Directive 1: Code Decoupling & Modularization Plan
- Define the optimal PSR-4 namespace and directory structure to split `redsea-ai-engine.php` into maintainable domain modules.
- Propose a Composer-based dependency management architecture for the WordPress plugin.

### 🎯 Directive 2: Enterprise Multi-Tenant Gateway Architecture
- Propose an enterprise-grade scaling architecture for `rsd-wa-gateway` capable of handling 50+ concurrent client WhatsApp instances without socket degradation.
- Provide recommendations for persistent Redis session storage and clustering.

### 🎯 Directive 3: Monetization & Engineering Roadmap Prioritization
- Group actionable architectural recommendations into a 3-tier milestone roadmap:
  - **P0 (Critical / Stability)**: Decoupling, Async Webhook Queues, Persistent Session Storage.
  - **P1 (High / Scalability)**: Multi-Tenancy, Automated Google Maps Prospecting Scraper, React Admin SPA.
  - **P2 (Nice-to-Have / Growth)**: Voice Call Integration (Twilio/WebRTC bridge), Multi-Currency Stripe/Paymob Escrow settlement.

---

**End of Technical Handover Dossier.**
