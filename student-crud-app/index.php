<?php
// index.php - main page serving HTML & API routes for CRUD operations
require_once __DIR__ . '/db.php';

// Simple router
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve API endpoints under /api/students
if (strpos($path, '/api/students') === 0) {
    $id = null;
    if (preg_match('/\/api\/students\/(\d+)/', $path, $matches)) {
        $id = (int) $matches[1];
    }
    header('Content-Type: application/json; charset=utf-8');

    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare('SELECT * FROM students WHERE id=?');
                $stmt->execute([$id]);
                $student = $stmt->fetch();
                echo json_encode($student ?: []);
            } else {
                $stmt = $pdo->query('SELECT * FROM students ORDER BY id DESC');
                echo json_encode($stmt->fetchAll());
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare('INSERT INTO students (name, email, course) VALUES (?, ?, ?)');
            $stmt->execute([$data['name'], $data['email'], $data['course']]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing ID']); break; }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare('UPDATE students SET name=?, email=?, course=? WHERE id=?');
            $stmt->execute([$data['name'], $data['email'], $data['course'], $id]);
            echo json_encode(['updated' => true]);
            break;
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing ID']); break; }
            $stmt = $pdo->prepare('DELETE FROM students WHERE id=?');
            $stmt->execute([$id]);
            echo json_encode(['deleted' => true]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
    }
    exit;
}

// If not API request, serve HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student CRUD App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Student Records</h1>
        <form id="studentForm" class="row g-3">
            <input type="hidden" id="studentId" />
            <div class="col-md-4">
                <input required type="text" id="name" class="form-control" placeholder="Name" />
            </div>
            <div class="col-md-4">
                <input required type="email" id="email" class="form-control" placeholder="Email" />
            </div>
            <div class="col-md-3">
                <input required type="text" id="course" class="form-control" placeholder="Course" />
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>

        <table id="studentsTable" class="table table-striped mt-4">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Course</th><th>Actions</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

<script>
async function fetchStudents() {
    const res = await fetch('/api/students');
    const students = await res.json();
    const tbody = document.querySelector('#studentsTable tbody');
    tbody.innerHTML = '';
    students.forEach(s => {
        tbody.insertAdjacentHTML('beforeend', `<tr>
            <td>${s.name}</td>
            <td>${s.email}</td>
            <td>${s.course}</td>
            <td>
                <button class="btn btn-sm btn-secondary me-2" onclick="editStudent(${s.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteStudent(${s.id})">Delete</button>
            </td>
        </tr>`);
    });
}

document.getElementById('studentForm').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('studentId').value;
    const data = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        course: document.getElementById('course').value
    };
    if (id) {
        await fetch(`/api/students/${id}`, {
            method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data)
        });
    } else {
        await fetch('/api/students', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data)
        });
    }
    e.target.reset();
    document.getElementById('studentId').value='';
    fetchStudents();
});

async function editStudent(id) {
    const res = await fetch(`/api/students/${id}`);
    const s = await res.json();
    document.getElementById('studentId').value = s.id;
    document.getElementById('name').value = s.name;
    document.getElementById('email').value = s.email;
    document.getElementById('course').value = s.course;
}

async function deleteStudent(id) {
    if (!confirm('Delete this student?')) return;
    await fetch(`/api/students/${id}`, {method:'DELETE'});
    fetchStudents();
}

fetchStudents();
</script>
</body>
</html>
