# Student Grades System (Système de Notes) - Documentation

## 🎉 Implementation Complete!

The **Student Grades System** has been successfully implemented in your Laravel + Filament application with full role-based access control and mobile API endpoints.

---

## 📋 Overview

### What's Been Built:

1. ✅ **Database Migration** - `grades` table with proper relationships
2. ✅ **Grade Model** - Complete Eloquent model with relationships and helpers
3. ✅ **Filament Resource** - Full CRUD interface with role-based permissions
4. ✅ **API Endpoints** - Secure mobile API for students to view their grades
5. ✅ **Role-Based Access** - Different permissions for Admin, Secretaire, and Professeur

---

## 🗄️ Database Structure

### Grades Table Schema:

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT (PK) | Primary key |
| student_id | INT | Foreign key to student.idStudent |
| teacher_id | BIGINT | Foreign key to users.id (professor) |
| course_id | BIGINT | Foreign key to courses.id |
| classe_id | INT (nullable) | Denormalized class ID for faster queries |
| note | DECIMAL(5,2) | Grade score (0.00 - 20.00) |
| type | ENUM | Type of evaluation (see types below) |
| subject_name | VARCHAR(255) | Cached subject/course name |
| exam_date | DATE | Date of the exam/evaluation |
| comment | TEXT | Teacher comments or observations |
| semester | VARCHAR(50) | Semester (S1, S2, etc.) |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update timestamp |

### Available Grade Types:
- **Contrôle 1** - First test
- **Contrôle 2** - Second test
- **Examen Final** - Final exam
- **Examen Blanc** - Mock exam
- **Devoir** - Homework
- **TP** - Practical work
- **Projet** - Project

### Foreign Key Relationships:
- `teacher_id` → `users.id` (ON DELETE CASCADE)
- `course_id` → `courses.id` (ON DELETE CASCADE)
- `student_id` → `student.idStudent` (Application level, no DB constraint)

### Indexes:
- `idx_grades_student` - Fast lookups by student
- `idx_grades_teacher` - Fast lookups by teacher
- `idx_grades_course` - Fast lookups by course
- `idx_grades_classe` - Fast lookups by class
- `idx_grades_student_course_type` - Composite index for complex queries

---

## 💻 Filament Web Dashboard

### Navigation:
- **Icon:** Academic cap (🎓)
- **Label:** "Notes"
- **Sort Order:** 4
- **Badge:** Shows total grade count

### Role-Based Access:

#### 👨‍💼 Admin/Secretaire:
- ✅ View all grades from all students
- ✅ Create new grades for any student
- ✅ Edit any grade
- ✅ Delete any grade
- ✅ Filter by student, course, type, semester
- ✅ Bulk actions available

#### 👨‍🏫 Professeur:
- ✅ View only grades for students in their classes
- ✅ Create grades only for their students and courses
- ✅ Edit only grades they assigned
- ✅ Delete only their own grades
- ✅ Automatic filtering to their courses
- ❌ Cannot see other teachers' grades

### Features:

#### Create/Edit Form:
```
┌─────────────────────────────────────┐
│ Informations de la Note             │
├─────────────────────────────────────┤
│ Étudiant: [Select with search]      │
│ Matière/Cours: [Select with search] │
│ Note: [0-20] / 20                   │
│ Type: [Dropdown]                    │
│ Date de l'Examen: [Date picker]    │
│ Semestre: [S1 / S2]                 │
│ Commentaire: [Textarea]             │
└─────────────────────────────────────┘
```

#### Table View Columns:
- **Matricule** - Copyable badge
- **Étudiant** - Full name (searchable)
- **Matière** - Course title
- **Note** - Color-coded badge (/20)
- **Type** - Evaluation type badge
- **Date** - Exam date
- **Semestre** - Semester badge
- **Classe** - Class badge
- **Professeur** - (Hidden for professors)

#### Filters:
- Student (multi-select)
- Course/Subject (multi-select)
- Type (multi-select)
- Semester (multi-select)
- Passing grades (>= 10)
- Failing grades (< 10)

#### Grade Color Coding:
| Grade Range | Status | Color |
|-------------|--------|-------|
| 16.00 - 20.00 | Excellent | Green (success) |
| 14.00 - 15.99 | Très Bien | Blue (info) |
| 12.00 - 13.99 | Bien | Blue (primary) |
| 10.00 - 11.99 | Passable | Yellow (warning) |
| 0.00 - 9.99 | Insuffisant | Red (danger) |

---

## 📱 Mobile API Endpoints

### Base URL: `http://YOUR_IP:8000/api`

All endpoints require **Sanctum authentication** (Bearer token).

### 1. Get All Grades (Main Endpoint)

**Endpoint:** `POST /api/grades`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "subject_name": "Mathématiques",
      "note": 14.50,
      "note_formatted": "14.50",
      "type": "Contrôle 1",
      "status": "Très Bien",
      "color": "info",
      "is_passing": true,
      "exam_date": "2026-06-01",
      "exam_date_formatted": "01/06/2026",
      "semester": "S1",
      "comment": "Excellent travail!",
      "teacher_name": "Prof. Dupont",
      "created_at": "2026-06-04T14:30:00Z"
    }
  ],
  "statistics": {
    "total_grades": 10,
    "average": 13.45,
    "average_formatted": "13.45",
    "passing_rate": 80.00,
    "passing_rate_formatted": "80.0%",
    "highest_grade": 18.50,
    "lowest_grade": 8.00
  },
  "grouped_by_course": [
    {
      "subject_name": "Mathématiques",
      "total_grades": 3,
      "average": 14.00,
      "average_formatted": "14.00",
      "grades": [...]
    }
  ],
  "grouped_by_type": [
    {
      "type": "Contrôle 1",
      "total_grades": 5,
      "average": 13.20,
      "average_formatted": "13.20",
      "grades": [...]
    }
  ],
  "grouped_by_semester": [
    {
      "semester": "S1",
      "total_grades": 6,
      "average": 13.50,
      "average_formatted": "13.50"
    }
  ]
}
```

### 2. Get Grade Statistics

**Endpoint:** `POST /api/grades/statistics`

**Response:**
```json
{
  "success": true,
  "statistics": {
    "total_grades": 10,
    "average": 13.45,
    "highest_grade": 18.50,
    "lowest_grade": 8.00,
    "passing_count": 8,
    "failing_count": 2,
    "passing_rate": 80.00
  }
}
```

### 3. Get Grades by Course

**Endpoint:** `POST /api/grades/by-course`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "subject_name": "Mathématiques",
      "total_grades": 3,
      "average": 14.00,
      "average_formatted": "14.00",
      "highest": 16.50,
      "lowest": 12.00,
      "grades": [
        {
          "id": 1,
          "note": 14.50,
          "type": "Contrôle 1",
          "status": "Très Bien",
          "color": "info",
          "exam_date": "01/06/2026",
          "comment": "Bon travail"
        }
      ]
    }
  ]
}
```

### 4. Get Grades by Type

**Endpoint:** `POST /api/grades/by-type`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "type": "Contrôle 1",
      "total_grades": 5,
      "average": 13.20,
      "average_formatted": "13.20",
      "grades": [
        {
          "id": 1,
          "subject_name": "Mathématiques",
          "note": 14.50,
          "status": "Très Bien",
          "color": "info",
          "exam_date": "01/06/2026"
        }
      ]
    }
  ]
}
```

---

## 🔐 Security & Permissions

### Authentication:
- All API endpoints require Sanctum authentication
- Student can only access their own grades
- Token must be valid and not expired

### Authorization:

#### Web Dashboard:
```php
// Admin/Secretaire
- canViewAny() → true
- canCreate() → true
- canEdit($record) → true
- canDelete($record) → true

// Professeur
- canViewAny() → true (filtered to their courses)
- canCreate() → true (filtered to their students)
- canEdit($record) → true (only their own grades)
- canDelete($record) → true (only their own grades)
```

#### Query Filtering:
```php
// Professor query is automatically filtered
if ($user->role === 'professeur') {
    $courseIds = Course::where('professor_id', $user->id)->pluck('id');
    $query->whereIn('course_id', $courseIds);
}
```

---

## 🧪 Testing Instructions

### 1. Web Dashboard Testing

#### As Admin/Secretaire:
```bash
1. Login: http://localhost:8000/admin
2. Navigate to "Notes" in sidebar
3. Click "Ajouter une Note"
4. Select a student, course, enter grade (0-20)
5. Select type (Contrôle 1, etc.)
6. Add comment (optional)
7. Save
8. Verify grade appears in table
9. Test filters (by student, course, type)
10. Test edit and delete
```

#### As Professeur:
```bash
1. Login with professor credentials
2. Navigate to "Notes"
3. Verify only students from your classes appear
4. Verify only your courses appear in dropdown
5. Create a grade
6. Verify you can only edit/delete your own grades
7. Verify badge shows your grade count
```

### 2. Mobile API Testing

#### Get All Grades:
```bash
curl -X POST http://192.168.100.99:8000/api/grades \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

#### Expected Response:
- HTTP 200
- JSON with data array, statistics, grouped data
- Dynamic URLs use your IP (not localhost)

#### Get Statistics:
```bash
curl -X POST http://192.168.100.99:8000/api/grades/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Get by Course:
```bash
curl -X POST http://192.168.100.99:8000/api/grades/by-course \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Get by Type:
```bash
curl -X POST http://192.168.100.99:8000/api/grades/by-type \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📁 Files Created/Modified

### ✅ Created Files (9):

1. **Database:**
   - `database/migrations/2026_06_04_141601_create_grades_table.php`

2. **Models:**
   - `app/Models/Grade.php`

3. **Controllers:**
   - `app/Http/Controllers/GradeController.php`

4. **Filament Resources:**
   - `app/Filament/Resources/GradeResource.php`
   - `app/Filament/Resources/GradeResource/Pages/ListGrades.php`
   - `app/Filament/Resources/GradeResource/Pages/CreateGrade.php`
   - `app/Filament/Resources/GradeResource/Pages/EditGrade.php`

5. **Documentation:**
   - `GRADES_SYSTEM_DOCUMENTATION.md` (this file)
   - `GRADES_API_EXAMPLES.md` (API usage examples)

### ✅ Modified Files (5):

1. **Models:**
   - `app/Models/Student.php` - Added `grades()` relationship
   - `app/Models/User.php` - Added `assignedGrades()` relationship
   - `app/Models/Course.php` - Added `grades()` relationship

2. **Routes:**
   - `routes/api.php` - Added 4 grade API endpoints

---

## 🎨 Flutter UI Screens (To Build)

### Recommended Screens:

#### 1. Grades Dashboard Screen
```
┌────────────────────────────────┐
│ 📊 Mes Notes                   │
├────────────────────────────────┤
│ Statistiques:                  │
│ • Moyenne: 13.45 / 20          │
│ • Réussite: 80%                │
│ • Total: 10 notes              │
├────────────────────────────────┤
│ Par Matière │ Par Type │ Liste │
├────────────────────────────────┤
│ [Grade cards/list here]        │
└────────────────────────────────┘
```

#### 2. Grade Detail Screen
```
┌────────────────────────────────┐
│ Mathématiques                  │
├────────────────────────────────┤
│ Note: 14.50 / 20               │
│ Appréciation: Très Bien        │
│ Type: Contrôle 1               │
│ Date: 01/06/2026               │
│ Semestre: S1                   │
│ Professeur: Prof. Dupont       │
│                                │
│ Commentaire:                   │
│ "Excellent travail..."         │
└────────────────────────────────┘
```

#### 3. Grades by Subject Screen
```
┌────────────────────────────────┐
│ Notes par Matière              │
├────────────────────────────────┤
│ 📚 Mathématiques               │
│    Moyenne: 14.00 (3 notes)    │
│    ├─ Contrôle 1: 14.50        │
│    ├─ Contrôle 2: 13.00        │
│    └─ Examen: 14.50            │
├────────────────────────────────┤
│ 🔬 Physique                    │
│    Moyenne: 12.50 (2 notes)    │
└────────────────────────────────┘
```

### Flutter Models:

```dart
class Grade {
  final int id;
  final String subjectName;
  final double note;
  final String noteFormatted;
  final String type;
  final String status;
  final String color;
  final bool isPassing;
  final String? examDate;
  final String? examDateFormatted;
  final String? semester;
  final String? comment;
  final String teacherName;
  final String? createdAt;
}

class GradeStatistics {
  final int totalGrades;
  final double average;
  final String averageFormatted;
  final double passingRate;
  final String passingRateFormatted;
  final double highestGrade;
  final double lowestGrade;
}

class GradeByCourse {
  final String subjectName;
  final int totalGrades;
  final double average;
  final String averageFormatted;
  final List<Grade> grades;
}
```

---

## 🚀 Next Steps

### For Backend (Done ✅):
- ✅ Database migration created and run
- ✅ Models with relationships
- ✅ Filament resource with role-based access
- ✅ API endpoints for mobile
- ✅ Dynamic URL middleware working

### For Flutter App (Your Turn 🎨):

1. **Create Grade Models** - Dart classes matching API response
2. **Create Grade Service** - API calls to fetch grades
3. **Create Grade Screens:**
   - Grades dashboard with statistics
   - Grades list view
   - Grade detail view
   - Grades by course view
   - Grades by type view
4. **Add Navigation** - Add to main app navigation
5. **Design UI** - Cards, charts, filters
6. **Test** - With real data from API

---

## 📊 Sample Data for Testing

### Insert Test Grades (via Tinker or Filament):

```php
php artisan tinker

// Example grades
Grade::create([
    'student_id' => 1,
    'teacher_id' => 2,
    'course_id' => 1,
    'classe_id' => 1,
    'note' => 14.50,
    'type' => 'Contrôle 1',
    'subject_name' => 'Mathématiques',
    'exam_date' => '2026-06-01',
    'semester' => 'S1',
    'comment' => 'Bon travail, continuez!',
]);

Grade::create([
    'student_id' => 1,
    'teacher_id' => 2,
    'course_id' => 1,
    'classe_id' => 1,
    'note' => 16.00,
    'type' => 'Contrôle 2',
    'subject_name' => 'Mathématiques',
    'exam_date' => '2026-06-15',
    'semester' => 'S1',
]);
```

---

## 🔧 Troubleshooting

### Issue: Grades not showing for professor
**Fix:** Ensure professor has courses assigned with `professor_id` = their user ID

### Issue: Student foreign key error
**Fix:** Foreign key constraint is intentionally disabled due to legacy schema. Relationships work at application level.

### Issue: API returns empty data
**Fix:** Ensure student has grades in database with correct `student_id`

### Issue: Can't create grade
**Fix:** Ensure student, teacher, and course IDs exist in respective tables

---

## 📞 Support

For questions or issues:
- Check Laravel logs: `storage/logs/laravel.log`
- Check Filament panel at `/admin`
- Test API with Postman or curl
- Review this documentation

---

**🎉 Congratulations! The Grades System is now ready for Flutter integration!**

Let's rock and build amazing grade screens! 🚀🔥
