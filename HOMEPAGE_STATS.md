# Homepage Statistics — Design Proposal

Platform: **RankED** — gamified educational course management.
Chart library: **vue-chartjs** (Chart.js wrapper).

---

## Global Statistics (visible to everyone)

### 1. Course Completion Rate by Subject — Doughnut Chart
**Chart:** `DoughnutChart`
**Data:** Average `Progression.percentage` grouped by `Matiere.libelle`.
**Why:** Gives a platform-wide snapshot of which subjects students engage with most and where completion drops off.

### 2. Top 5 Most Completed Courses — Horizontal Bar Chart
**Chart:** `BarChart` (indexAxis: 'y')
**Data:** Courses ordered by average `Progression.percentage` DESC, limit 5. (Endpoint `/api/courses/top5` already exists.)
**Why:** Highlights the most engaging content and serves as social proof for new users.

### 3. Active Students per Class — Bar Chart
**Chart:** `BarChart`
**Data:** Count of `Eleve` per `Classe`, filtered to those with at least one `Progression` record.
**Why:** Lets teachers and admins spot which classes are actively using the platform.

### 4. Badge Distribution — Pie Chart
**Chart:** `PieChart`
**Data:** Count of `Progression` records grouped by `Badge.type`.
**Why:** Shows the spread of achievements across the platform — a quick health indicator for the gamification system.

### 5. New Registrations Over Time — Line Chart
**Chart:** `LineChart`
**Data:** Count of `User` created per week over the last 8 weeks.
**Why:** Tracks platform growth and adoption trends.

---

## Personal Statistics (logged-in user only)

### 6. My Progression per Course — Horizontal Bar Chart
**Chart:** `BarChart` (indexAxis: 'y')
**Data:** `Progression.percentage` for each `Cours` the student is enrolled in.
**Why:** The primary self-assessment tool — students can see at a glance which courses need attention.
**Colour coding:** Red < 40 %, Orange 40–75 %, Green > 75 %.

### 7. My Competences Acquired — Radar Chart
**Chart:** `RadarChart`
**Data:** For each `Matiere`, count of `EleveCompetence` records divided by total `Competence` records in that subject (as a percentage).
**Why:** Radar charts are ideal for multi-axis skill profiles; students get a visual fingerprint of their strengths.

### 8. My Quiz Score History — Line Chart
**Chart:** `LineChart`
**Data:** `Qcm.gainPts` earned over time (ordered by activity completion date).
**Why:** Shows learning momentum — a rising curve is motivating; a plateau signals a need for review.

### 9. My Badges Earned — Doughnut Chart
**Chart:** `DoughnutChart`
**Data:** Count of badges earned by `Badge.type` for the current student.
**Why:** Keeps the gamification loop visible and personal — different from the global badge chart.

### 10. My Rank in Class — Gauge / single Bar
**Chart:** `BarChart` (single horizontal bar showing position X out of N)
**Data:** Student's average `Progression.percentage` vs. classmates' averages, expressed as a percentile.
**Why:** Competitive element — the "ranked" core concept made tangible without exposing raw scores of peers.

---

## Chart-to-Component Mapping

| Stat | vue-chartjs component | Key dataset field |
|------|-----------------------|-------------------|
| 1. Completion by Subject | `DoughnutChart` | avg(percentage) per matiere |
| 2. Top 5 Courses | `BarChart` | avg(percentage) |
| 3. Active Students / Class | `BarChart` | count(eleve) |
| 4. Badge Distribution | `PieChart` | count per badge.type |
| 5. Registrations Over Time | `LineChart` | count(user) per week |
| 6. My Progression | `BarChart` | percentage per cours |
| 7. My Competences | `RadarChart` | acquired/total per matiere |
| 8. My Quiz Scores | `LineChart` | gainPts over time |
| 9. My Badges | `DoughnutChart` | count per badge.type |
| 10. My Class Rank | `BarChart` | percentile position |

---

## API Endpoints Needed

| Stat | Endpoint (suggested) | Notes |
|------|-----------------------|-------|
| 1 | `GET /api/stats/completion-by-subject` | Public or role-gated |
| 2 | `GET /api/courses/top5` | Already exists |
| 3 | `GET /api/stats/active-students-per-class` | Teacher/admin only |
| 4 | `GET /api/stats/badge-distribution` | Public |
| 5 | `GET /api/stats/registrations` | Admin only |
| 6 | `GET /api/my-progressions` | Student — already partially exists |
| 7 | `GET /api/my-competences/radar` | Student |
| 8 | `GET /api/my-quiz-scores` | Student |
| 9 | `GET /api/my-badges` | Student |
| 10 | `GET /api/my-class-rank` | Student |
