# Grades API - Usage Examples for Flutter

## 🎯 Quick Reference

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/grades` | POST | Get all grades with statistics and groupings |
| `/api/grades/statistics` | POST | Get only statistics |
| `/api/grades/by-course` | POST | Get grades grouped by subject |
| `/api/grades/by-type` | POST | Get grades grouped by exam type |

---

## 🔑 Authentication

All endpoints require Sanctum Bearer token authentication.

### Headers:
```dart
final headers = {
  'Authorization': 'Bearer ${AppConstants.token}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
};
```

---

## 📱 Flutter Service Class

### GradeService.dart

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/app_constants.dart';
import '../models/grade.dart';
import '../models/grade_statistics.dart';

class GradeService {
  static const String baseUrl = AppConstants.baseUrl;

  // Get all grades with full statistics and groupings
  static Future<Map<String, dynamic>> getAllGrades() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/grades'),
        headers: {
          'Authorization': 'Bearer ${AppConstants.token}',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return {
            'grades': (data['data'] as List)
                .map((json) => Grade.fromJson(json))
                .toList(),
            'statistics': GradeStatistics.fromJson(data['statistics']),
            'groupedByCourse': (data['grouped_by_course'] as List)
                .map((json) => GradeByCourse.fromJson(json))
                .toList(),
            'groupedByType': (data['grouped_by_type'] as List)
                .map((json) => GradeByType.fromJson(json))
                .toList(),
            'groupedBySemester': data['grouped_by_semester'] ?? [],
          };
        }
      }
      
      throw Exception('Failed to load grades');
    } catch (e) {
      print('Error fetching grades: $e');
      rethrow;
    }
  }

  // Get just the statistics
  static Future<GradeStatistics> getStatistics() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/grades/statistics'),
        headers: {
          'Authorization': 'Bearer ${AppConstants.token}',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return GradeStatistics.fromJson(data['statistics']);
        }
      }
      
      throw Exception('Failed to load statistics');
    } catch (e) {
      print('Error fetching statistics: $e');
      rethrow;
    }
  }

  // Get grades grouped by course/subject
  static Future<List<GradeByCourse>> getGradesByCourse() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/grades/by-course'),
        headers: {
          'Authorization': 'Bearer ${AppConstants.token}',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return (data['data'] as List)
              .map((json) => GradeByCourse.fromJson(json))
              .toList();
        }
      }
      
      throw Exception('Failed to load grades by course');
    } catch (e) {
      print('Error fetching grades by course: $e');
      rethrow;
    }
  }

  // Get grades grouped by exam type
  static Future<List<GradeByType>> getGradesByType() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/grades/by-type'),
        headers: {
          'Authorization': 'Bearer ${AppConstants.token}',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          return (data['data'] as List)
              .map((json) => GradeByType.fromJson(json))
              .toList();
        }
      }
      
      throw Exception('Failed to load grades by type');
    } catch (e) {
      print('Error fetching grades by type: $e');
      rethrow;
    }
  }
}
```

---

## 📦 Flutter Models

### 1. Grade Model

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

  Grade({
    required this.id,
    required this.subjectName,
    required this.note,
    required this.noteFormatted,
    required this.type,
    required this.status,
    required this.color,
    required this.isPassing,
    this.examDate,
    this.examDateFormatted,
    this.semester,
    this.comment,
    required this.teacherName,
    this.createdAt,
  });

  factory Grade.fromJson(Map<String, dynamic> json) {
    return Grade(
      id: json['id'] as int,
      subjectName: json['subject_name'] as String,
      note: (json['note'] as num).toDouble(),
      noteFormatted: json['note_formatted'] as String,
      type: json['type'] as String,
      status: json['status'] as String,
      color: json['color'] as String,
      isPassing: json['is_passing'] as bool,
      examDate: json['exam_date'] as String?,
      examDateFormatted: json['exam_date_formatted'] as String?,
      semester: json['semester'] as String?,
      comment: json['comment'] as String?,
      teacherName: json['teacher_name'] as String,
      createdAt: json['created_at'] as String?,
    );
  }

  // Get color for Flutter UI
  Color getFlutterColor() {
    switch (color) {
      case 'success':
        return Colors.green;
      case 'info':
        return Colors.blue;
      case 'primary':
        return Colors.indigo;
      case 'warning':
        return Colors.orange;
      case 'danger':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
```

### 2. Grade Statistics Model

```dart
class GradeStatistics {
  final int totalGrades;
  final double average;
  final String averageFormatted;
  final double passingRate;
  final String passingRateFormatted;
  final double highestGrade;
  final double lowestGrade;

  GradeStatistics({
    required this.totalGrades,
    required this.average,
    required this.averageFormatted,
    required this.passingRate,
    required this.passingRateFormatted,
    required this.highestGrade,
    required this.lowestGrade,
  });

  factory GradeStatistics.fromJson(Map<String, dynamic> json) {
    return GradeStatistics(
      totalGrades: json['total_grades'] as int,
      average: (json['average'] as num).toDouble(),
      averageFormatted: json['average_formatted'] as String,
      passingRate: (json['passing_rate'] as num).toDouble(),
      passingRateFormatted: json['passing_rate_formatted'] as String,
      highestGrade: (json['highest_grade'] as num).toDouble(),
      lowestGrade: (json['lowest_grade'] as num).toDouble(),
    );
  }
}
```

### 3. Grade By Course Model

```dart
class GradeByCourse {
  final String subjectName;
  final int totalGrades;
  final double average;
  final String averageFormatted;
  final List<GradeItem> grades;

  GradeByCourse({
    required this.subjectName,
    required this.totalGrades,
    required this.average,
    required this.averageFormatted,
    required this.grades,
  });

  factory GradeByCourse.fromJson(Map<String, dynamic> json) {
    return GradeByCourse(
      subjectName: json['subject_name'] as String,
      totalGrades: json['total_grades'] as int,
      average: (json['average'] as num).toDouble(),
      averageFormatted: json['average_formatted'] as String,
      grades: (json['grades'] as List)
          .map((item) => GradeItem.fromJson(item))
          .toList(),
    );
  }
}

class GradeItem {
  final int id;
  final double note;
  final String type;
  final String status;
  final String? examDate;
  final String? comment;

  GradeItem({
    required this.id,
    required this.note,
    required this.type,
    required this.status,
    this.examDate,
    this.comment,
  });

  factory GradeItem.fromJson(Map<String, dynamic> json) {
    return GradeItem(
      id: json['id'] as int,
      note: (json['note'] as num).toDouble(),
      type: json['type'] as String,
      status: json['status'] as String,
      examDate: json['exam_date'] as String?,
      comment: json['comment'] as String?,
    );
  }
}
```

### 4. Grade By Type Model

```dart
class GradeByType {
  final String type;
  final int totalGrades;
  final double average;
  final String averageFormatted;
  final List<GradeTypeItem> grades;

  GradeByType({
    required this.type,
    required this.totalGrades,
    required this.average,
    required this.averageFormatted,
    required this.grades,
  });

  factory GradeByType.fromJson(Map<String, dynamic> json) {
    return GradeByType(
      type: json['type'] as String,
      totalGrades: json['total_grades'] as int,
      average: (json['average'] as num).toDouble(),
      averageFormatted: json['average_formatted'] as String,
      grades: (json['grades'] as List)
          .map((item) => GradeTypeItem.fromJson(item))
          .toList(),
    );
  }
}

class GradeTypeItem {
  final int id;
  final String subjectName;
  final double note;
  final String status;
  final String? examDate;

  GradeTypeItem({
    required this.id,
    required this.subjectName,
    required this.note,
    required this.status,
    this.examDate,
  });

  factory GradeTypeItem.fromJson(Map<String, dynamic> json) {
    return GradeTypeItem(
      id: json['id'] as int,
      subjectName: json['subject_name'] as String,
      note: (json['note'] as num).toDouble(),
      status: json['status'] as String,
      examDate: json['exam_date'] as String?,
    );
  }
}
```

---

## 🎨 Sample UI Widgets

### 1. Statistics Card

```dart
class StatisticsCard extends StatelessWidget {
  final GradeStatistics statistics;

  const StatisticsCard({required this.statistics});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 4,
      margin: EdgeInsets.all(16),
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            Text(
              'Statistiques',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatItem(
                  'Moyenne',
                  statistics.averageFormatted,
                  '/ 20',
                  Colors.blue,
                ),
                _buildStatItem(
                  'Réussite',
                  statistics.passingRateFormatted,
                  '',
                  Colors.green,
                ),
                _buildStatItem(
                  'Total',
                  statistics.totalGrades.toString(),
                  'notes',
                  Colors.orange,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value, String suffix, Color color) {
    return Column(
      children: [
        Text(label, style: TextStyle(fontSize: 12, color: Colors.grey)),
        SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color),
        ),
        if (suffix.isNotEmpty)
          Text(suffix, style: TextStyle(fontSize: 12, color: Colors.grey)),
      ],
    );
  }
}
```

### 2. Grade Card

```dart
class GradeCard extends StatelessWidget {
  final Grade grade;

  const GradeCard({required this.grade});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: grade.getFlutterColor(),
          child: Text(
            grade.noteFormatted,
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
        ),
        title: Text(
          grade.subjectName,
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${grade.type} - ${grade.status}'),
            if (grade.examDateFormatted != null)
              Text('Date: ${grade.examDateFormatted}',
                  style: TextStyle(fontSize: 12)),
          ],
        ),
        trailing: Icon(
          grade.isPassing ? Icons.check_circle : Icons.cancel,
          color: grade.isPassing ? Colors.green : Colors.red,
        ),
        onTap: () {
          // Navigate to grade detail
        },
      ),
    );
  }
}
```

### 3. Grades Screen Example

```dart
class GradesScreen extends StatefulWidget {
  @override
  _GradesScreenState createState() => _GradesScreenState();
}

class _GradesScreenState extends State<GradesScreen> {
  bool _isLoading = true;
  List<Grade> _grades = [];
  GradeStatistics? _statistics;

  @override
  void initState() {
    super.initState();
    _loadGrades();
  }

  Future<void> _loadGrades() async {
    try {
      final data = await GradeService.getAllGrades();
      setState(() {
        _grades = data['grades'];
        _statistics = data['statistics'];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Mes Notes'),
        backgroundColor: Colors.indigo,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadGrades,
              child: ListView(
                children: [
                  if (_statistics != null)
                    StatisticsCard(statistics: _statistics!),
                  Padding(
                    padding: EdgeInsets.all(16),
                    child: Text(
                      'Toutes les notes',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                  ),
                  ..._grades.map((grade) => GradeCard(grade: grade)),
                ],
              ),
            ),
    );
  }
}
```

---

## 🧪 Testing with Postman/cURL

### 1. Get All Grades

```bash
curl -X POST http://192.168.100.99:8000/api/grades \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 2. Get Statistics

```bash
curl -X POST http://192.168.100.99:8000/api/grades/statistics \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 3. Get Grades by Course

```bash
curl -X POST http://192.168.100.99:8000/api/grades/by-course \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### 4. Get Grades by Type

```bash
curl -X POST http://192.168.100.99:8000/api/grades/by-type \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

---

## 🎯 Pro Tips

1. **Cache Data:** Cache grades locally to reduce API calls
2. **Pull to Refresh:** Implement pull-to-refresh for fresh data
3. **Empty States:** Show friendly message when no grades exist
4. **Error Handling:** Handle network errors gracefully
5. **Loading States:** Show skeletons/shimmer while loading
6. **Charts:** Use fl_chart package for grade visualizations
7. **Filters:** Add client-side filtering by semester, subject, etc.

---

**🚀 Ready to build the Flutter UI? You have all the API endpoints and models you need!**
