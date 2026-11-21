# RiderHub — Weekly & Daily Capstone Development Plan (Week 1 to Week 5)

**Project:** RiderHub — Ride-Booking, Express Delivery & Live GPS Mobility Platform (Misamis Oriental)  
**Live Production URL:** https://riderhub.online  
**GitHub Repository:** https://github.com/drixizll/riderhub  
**Developer:** Drexell Jhon G. Vallecera  

---

## Week 1 — Foundation, Authentication & Role-Based Architecture (100% Completed)
| Day | Task / Deliverable | Status |
|---|---|---|
| Day 1 | Project initialization (Laravel 12, Tailwind CSS, Alpine.js, Vite, MySQL/SQLite database design) | Completed |
| Day 2 | Multi-role authentication system (`customer`, `rider`, `admin`) with role middleware protection | Completed |
| Day 3 | Customer, Rider, and Admin database schema (`users`, `bookings`, `payments`, `rider_locations`, `booking_ratings`, `notifications`) | Completed |
| Day 4 | Account settings, profile update, password security, and remember-me quick sign-in chips | Completed |
| Day 5 | Automated feature test suite setup for authentication and role guards | Completed |

---

## Week 2 — Core Ride & Express Delivery Booking + PayMongo Integration (100% Completed)
| Day | Task / Deliverable | Status |
|---|---|---|
| Day 1 | Interactive Leaflet route pinning & Haversine fare calculator (₱55 base + ₱18.50/km) | Completed |
| Day 2 | Ride & Package Delivery booking workflow with 50m minimum distance validation | Completed |
| Day 3 | PayMongo `/v1/checkout_sessions` integration (GCash, Maya, Visa/Mastercard) + Cash to Rider | Completed |
| Day 4 | PayMongo webhook signature verification + automated `paymongo:sync` background recovery | Completed |
| Day 5 | Safe pending booking cancellation with row locking & automatic PayMongo session expiration | Completed |

---

## Week 3 — Misamis Oriental Live GPS, Road Routing & AI Voice Navigation (100% Completed)
| Day | Task / Deliverable | Status |
|---|---|---|
| Day 1 | Expanded Misamis Oriental service bounds (Tagoloan, CDO, Villanueva, Jasaan, Opol, El Salvador, Balingasag, Gingoog) | Completed |
| Day 2 | Multi-layer map switcher (Street Map, Esri Satellite Hybrid, OpenStreetMap) + quick barangay/town pins | Completed |
| Day 3 | Real-time Rider GPS location broadcasting & live customer tracking map | Completed |
| Day 4 | OSRM real road routing (`GET /api/route`) + AI Voice Turn-by-Turn Navigator (Web Speech API) for Riders | Completed |
| Day 5 | Inline Pickup/Drop-off location editor for pending bookings with live fare recalculation | Completed |

---

## Week 4 — Royal Violet UI Redesign, Analytics Charts, Reports & AI Assistant (100% Completed)
| Day | Task / Deliverable | Status |
|---|---|---|
| Day 1 | Royal Violet brand theme (`#7c3aed`), 60fps interactive mobility network canvas, and dark/light mode | Completed |
| Day 2 | Interactive Chart.js 7-day analytics & status breakdown charts across Admin, Customer, and Rider dashboards | Completed |
| Day 3 | Hover-to-expand workspace sidebar + animated Executive PDF/CSV reports & official trip receipts | Completed |
| Day 4 | Live Open-Meteo Misamis Oriental weather advisory widget (`GET /api/weather`) | Completed |
| Day 5 | RiderHub AI Assistant (`POST /api/assistant/chat`) with live Groq LLM, fare/weather/booking awareness & Taglish/Bisaya support | Completed |

---

## Week 5 — Decoupled API v1, Mobile iOS/Android Polish, Security OTP & Production Deployment (100% Completed)
| Day | Task / Deliverable | Status |
|---|---|---|
| Day 1 | Admin People Directory management (Edit/Delete user accounts with self-protection & custom confirmation modal) | Completed |
| Day 2 | 2-Step 6-Digit Email/Phone OTP Password Recovery + live SMTP & in-app security notifications | Completed |
| Day 3 | Mobile iOS & Android responsiveness audit (Retina high-DPI canvas, client-side photo compression, direct media route, centered notifications) | Completed |
| Day 4 | Decoupled Architecture Phase 1: Backend REST API v1 (`/api/v1/*` with Bearer token auth) + standalone `frontend/` client | Completed |
| Day 5 | Repohive Cloud CI/CD deployment (`riderhub.online`), Repohive AI/OTP integration, and final 80+ automated PHPUnit tests verification | Completed |
