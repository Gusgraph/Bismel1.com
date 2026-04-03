# Bismel1

Bismel1 is a premium AI-powered stock trading automation platform being built as a real product, not a mock dashboard.

It is designed to give traders a cleaner product experience from landing page through broker connection, automation readiness, runtime visibility, and account management.

---

## Product Goal

Build a premium trading platform frontend and app layer in Laravel that:

- presents Bismel1 as an AI-powered trading automation product
- connects users into Alpaca-based broker workflows
- gives clear visibility into runtime state, orders, positions, and broker readiness
- feels like a premium AI trading terminal, not generic SaaS

---

## Core Product Purpose

Bismel1 is meant to help traders:

- scan and evaluate market opportunities with clearer signal context
- manage automation workflows with better visibility
- monitor positions, orders, broker sync, and runtime readiness
- operate from a cleaner AI-trading control surface

Public-facing product language currently leans on:

- AI-powered trading automation
- real-time market intelligence
- signal clarity
- execution visibility
- operator-grade control

---

## Current Architecture

The system is intentionally split.

### Laravel
Laravel is the frontend, app, and product layer.

It handles:

- landing pages
- login / signup
- dashboard UI
- billing and product pages
- broker connection forms
- user-facing runtime/account views
- later admin views

### FastAPI / Python Executor
The execution engine is separate from Laravel.

It handles:

- webhook intake
- execution logic
- broker-aware execution workflows
- runtime processing
- backend service behavior

### Other Services

- **Alpaca**: broker, account, positions, orders, trading integration
- **Stripe**: billing
- **Firestore**: provisioning/runtime data for now

---

## Intended User Flow

### Public Side

- homepage / landing page
- plans page
- login / signup
- product positioning around AI trading automation and market intelligence

### Authenticated App Side

- user dashboard
- broker connection flow
- Alpaca account connection and sync
- automation readiness / runtime state
- positions / orders / activity
- account / billing / settings
- later admin views

---

## Stock Strategy Direction

Bismel1 is a stock-trading product.

Current strategy direction is centered around:

- stock-focused workflows
- momentum / catalyst / market-structure aware scanning
- execution-aware operator visibility
- market intelligence product positioning
- clean signal-to-action workflow

This is not meant to be a generic finance UI.

---

## Current Repo Reality

Current live working path on the VM:

`/var/www/html/bismel1.com`

This folder is the source of truth.

Important notes:

- old path `/var/www/html/gusgraph-trading` was removed
- repo remote is GitHub
- VM-side edits are done directly inside the domain folder
- homepage and guest layout are under active redesign
- Laravel guest/public product shell is being shaped first
- market background JS is already integrated and working

---

## Main Tech Stack

- Laravel
- Blade
- Vite
- Custom CSS / JS
- FastAPI / Python executor
- Alpaca
- Stripe
- Firestore
- GitHub

---

## Main Files Currently Important

Frontend / landing work has been centered around:

- `resources/views/layouts/guest.blade.php`
- `resources/views/home.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/market-background.js`

---

## VM Workflow

Main VM context:

- GCP VM: `servgraph-vm-1`
- VM user: `gusgraphy`
- web root: `/var/www/html`
- active domain repo: `/var/www/html/bismel1.com`

Typical session start:

```bash
cd /var/www/html/bismel1.com
cat project-tracking/00-START-HERE.md
cat project-tracking/01-CURRENT-STATUS.md
cat project-tracking/05-ACTIVE-TASKS.md
cat project-tracking/09-GIT-STATE.md
