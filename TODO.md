# Energy App Dashboard Implementation TODO

## Overall Plan
Implement compatible dashboards using existing Laravel APIs:
- **User Dashboard**: Personal meters, readings, consumption trends.
- **Admin Dashboard**: Global stats, user management, exports.

## Step-by-Step Tasks

### 1. Setup & Dependencies ✅ (Plan approved)
- [x] Created TODO.md

### 2. Complete Redux Slices (Next)
- [x] Implement `metersSlice.js`: RTK Query endpoints for `my-meters`, `meters-with-readings`, meter-specific (monthly-data, compare-months, average-comparison).
- [x] Implement `readingsSlice.js`: Endpoints for meter readings, date-range readings.
- [x] Implement `statsSlice.js`: Admin endpoints (`admin/statistics`, `admin/consumption-stats`).
- [x] Update `store.js`: Import statsSlice + RTK Query middleware.
- [x] Test: Check network tab for API calls after login.
  - [ ] cd frontend && npm i recharts (completed).

### 3. User Dashboard (Dashboard.jsx) ✅
- [x] Fetch user meters/readings via slices.
- [x] Stats cards: Total meters/readings, avg consumption.
- [x] Meter cards/grid: Name/type/latest reading/trend indicator.
- [x] Monthly line charts per meter (Recharts).
- [x] Recent readings table.
- [x] Responsive Tailwind layout.

### 4. Admin Dashboard (AdminDashboard.jsx) ✅
- [x] Fetch global stats/consumption.
- [x] Global stats cards: Totals, monthly comparison.
- [x] Charts: Consumption by city/quartier (bar/pie), trends.
- [x] Users table with actions (edit/toggle/delete).
- [x] Export buttons (CSV/PDF).

### 5. UI Components & Polish
- [ ] Create reusable: `StatsCard.jsx`, `MeterCard.jsx`, `LineChart.jsx`, `BarChart.jsx` in `components/charts/`.
- [ ] Add loading/skeleton states, error handling.
- [ ] Filters (date range), search for admin table.

### 6. Testing & Demo
- [ ] Install recharts: `cd frontend && npm i recharts`.
- [ ] Seed data, test citizen/admin login.
- [ ] Responsive check (mobile/desktop).

**Progress: Track by checking [ ] → [x]. Update after each step.**

