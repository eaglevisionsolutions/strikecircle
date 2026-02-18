# StrikeCircle

**Connect. Compete. Bowl.**

StrikeCircle is a modern social networking platform built exclusively for the bowling community. It enables bowlers to connect with friends, track scores, join leagues, compete in tournaments, and build their competitive presence online.

---

## 🎯 Vision

StrikeCircle aims to become the digital home for bowlers worldwide — combining social networking, structured competition, and performance tracking into one unified platform.

---

## 🚀 Core Features

### 👤 Social Networking
- User profiles
- Friend connections
- Activity feed
- Direct messaging
- Notifications

### 🎳 Score Tracking
- Post game scores
- Track averages
- Record high games and series
- Upload score sheets
- View performance history

### 🏆 Leagues
- Create and join leagues
- Standings and schedules
- League chat
- Score submission and verification

### 🥇 Tournaments
- Single & double elimination brackets
- Round robin formats
- Online competitions
- Match score submissions
- Live updates

---

## 🧱 Platform Architecture (High-Level)

- Frontend: Modern responsive web application
- Backend: Secure API-driven architecture
- Database: Structured relational data model
- Authentication: Secure user account system
- Real-time features: Notifications and messaging

---

## 📱 Design Principles

- Light UI (clean and familiar social layout)
- Blue-accented brand system
- Scalable component-based design
- Mobile-first responsiveness
- Minimal and modern interface

---

## 🔒 License

Copyright (c) 2026 StrikeCircle

All rights reserved.

This software and its source code are proprietary and confidential.  
Unauthorized copying, modification, distribution, sublicensing, or commercial use of this software is strictly prohibited without express written permission from the copyright holder.

The publication of this repository does not grant any license to use, copy, modify, or distribute this software.

---

## 📦 Database Seeding & Demo Data

StrikeCircle provides a robust, deterministic seeding system for generating rich demo data for development and UI testing. All seeders use factory classes for realistic, repeatable data.

### Seeding the Database

Run all seeders (recommended for local/dev):

```sh
php scripts/seed.php
```

To wipe all tables before seeding (safe for local/dev):

```sh
php scripts/seed.php --fresh
```

To run a specific seeder:

```sh
php scripts/seed.php --class=UserSeeder
```

> **Note:** Seeding is disabled in production for safety.

### Deterministic Factories

- All demo data is generated using factories with a deterministic random seed (set via `SEED_DATA_SEED` env var).
- Data is always the same for a given seed, ensuring stable UI and test scenarios.
- Factories exist for users, posts, scores, friends, reactions, comments, leagues, tournaments, and messages.

---
## 📌 Status

StrikeCircle is currently under active development.

---

## 🌍 Future Roadmap

- Advanced performance analytics
- Mobile app release
- Premium memberships
- Sponsored tournaments
- Coaching marketplace
- Verified bowler profiles

---

## 🤝 Contributions

This repository is publicly viewable but not open-source.  
Contributions may be accepted at the discretion of the project owner.

Please contact the maintainer before submitting significant changes.

---

## 📫 Contact

For business inquiries or collaboration:
info@eaglevisionsolutions.ca

---

**StrikeCircle — The social network for bowlers.**