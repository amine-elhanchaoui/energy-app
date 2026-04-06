# ⚡ Energy Consumption Tracking — Frontend Guide

This guide provides a complete analysis and roadmap for building the React frontend of the Energy App, based on the existing Laravel backend.

---

## 📋 Task 1 — Full Frontend Analysis

### 1.1 Pages and Components
| Page/Component | Purpose | API Endpoints | Role | Priority |
| :--- | :--- | :--- | :--- | :--- |
| **Login / Register** | Auth entry points | `/login`, `/register` | Public | High |
| **Dashboard** | Overview of consumption & KPIs | `/user`, `/meters-with-readings`, `/admin/statistics` | Both | High |
| **My Meters** | List and view personal meters | `/my-meters` | Citoyen | High |
| **Meter Details** | Detailed history, charts, and info | `/meters/{id}`, `/meters/{id}/monthly-data` | Both | Medium |
| **Add Reading** | Form to submit meter values + photo | `/readings` (POST) | Citoyen | High |
| **User Management** | CRUD for users & status toggle | `/admin/users` (GET, POST, PUT, DELETE, PATCH) | Admin | High |
| **Consumption Stats** | Global maps/charts by city/quartier | `/admin/consumption-stats`, `/admin/statistics` | Admin | Medium |
| **Exports** | Download PDF/CSV reports | `/admin/readings/export-csv`, `/admin/readings/export-pdf` | Admin | Low |

### 1.2 Component Breakdown (Reusable)
- **`Layout`**: Sidebar/Navbar with role-based links.
- **`StatCard`**: Consumption KPIs (e.g., "Current vs Prev Month").
- **`ConsumptionChart`**: Recharts-based bar/line chart for trends.
- **`ReadingForm`**: Modal/Page for data entry (+ file upload).
- **`DataTable`**: Generic table with sorting/filtering for users and readings.
- **`AuthGuard`**: Component to protect routes based on Sanctum & Roles.

---

## 🔍 Task 2 — Gap Analysis

### 2.1 Missing Features
- **City/Quartier Lists**: The backend models exist, but `/api/cities` and `/api/quartiers` routes are missing for frontend dropdowns (needed for Registration & Meter creation).
- **Profile Management**: No endpoint for updating `name`, `email`, or user-specific info besides `change-password`.

### 2.2 Integration Considerations
- **Storage Link**: React needs the absolute URL for images (e.g., `http://localhost:8000/storage/...`). Ensure `php artisan storage:link` is run.
- **Role Permissions**: Frontend needs a centralized way to check `Spatie` roles returned in the user object.
- **Carbon Dates**: Ensure consistent date formatting between Laravel (ISO) and the frontend.

---

## 🚀 Task 3 — Detailed Roadmap

### Phase 1: Project Setup (Est: 4h)
- [ ] Initialize React + Tailwind 4.
- [ ] Configure Axios instance with `withCredentials: true` and interceptors.
- [ ] Set up folder structure and basic routing.

### Phase 2: Auth & Routing (Est: 6h)
- [ ] Implement Login/Register logic.
- [ ] Build AuthContext for state persistence.
- [ ] Create Protected Routes with role-based checks.

### Phase 3: Citoyen Features (Est: 12h)
- [ ] Dashboard with consumption KPIs & Recharts.
- [ ] Meter listing and Detail pages.
- [ ] Reading submission form with image upload preview.

### Phase 4: Admin Features (Est: 10h)
- [ ] User management table (CRUD + status toggle).
- [ ] Global statistics visualizers.
- [ ] Export functionality (Excel/PDF triggers).

### Phase 5: Polish & Opt. (Est: 8h)
- [ ] Form validations (Zod/Formik).
- [ ] Loading states (Skeletons) & Toast notifications.
- [ ] Responsive design & Dark mode optimization.

---

## 🛠️ Folder Structure Recommendation
```text
src/
├── api/          # Axios instance & specific API calls
├── components/   # Reusable UI (Buttons, Cards, Charts)
├── context/      # AuthContext, RoleContext
├── hooks/        # useAuth, useMeters, useStats
├── layouts/      # DashboardLayout, AuthLayout
├── pages/        # Full page views (Admin/Citizen)
├── utils/        # Date formatters, number grouping
└── App.js        # Routes definition
```

---

## 🔌 API Integration Guide
1. **CSRF**: Call `/sanctum/csrf-cookie` before any POST request.
2. **Persistence**: Use `localStorage` or `sessionStorage` for the user object, but rely on cookies for the session.
3. **Roles**: Handle `403 Forbidden` globally to redirect users back to their respective dashboards if they try to access unauthorized pages.
