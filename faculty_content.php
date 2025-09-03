<?php
$user_id = $_SESSION['user_id'];
// Fetch faculty info
$sql = "SELECT * FROM faculties WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();

echo "<h2>Welcome," . $faculty['name'] . "</h2>";
echo "<p>Email: " . $faculty['email'] . "</p>";
echo "<p>Department: " . $faculty['department'] . "</p>";

// Process Form Submissions
// 1. CREATE: Add a new course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $credit = $_POST['credit'];
    
    $insert_sql = "INSERT INTO courses (course_name, credit, faculty_id) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sii", $course_name, $credit, $faculty['faculty_id']);
    
    if ($insert_stmt->execute()) {
        echo "<div class='alert alert-success'>New course added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding course: " . $conn->error . "</div>";
    }
}

// 2. UPDATE: Update a grade
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_result'])) {
    $result_id = $_POST['result_id'];
    $new_grade = $_POST['new_grade'];
    $new_marks = $_POST['new_marks'];
    
    $update_sql = "UPDATE results SET grade=?, marks=? WHERE result_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $new_grade, $new_marks, $result_id);
    
    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Grade updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating grade: " . $conn->error . "</div>";
    }
}

// 3. DELETE: Delete a course
if (isset($_GET['delete_course'])) {
    $course_id = $_GET['delete_course'];
    
    // First, delete related results to avoid foreign key constraint error
    $delete_results_sql = "DELETE FROM results WHERE course_id=?";
    $delete_results_stmt = $conn->prepare($delete_results_sql);
    $delete_results_stmt->bind_param("i", $course_id);
    $delete_results_stmt->execute();
    
    // Then, delete the course
    $delete_sql = "DELETE FROM courses WHERE course_id=?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $course_id);
    
    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success'>Course deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting course: " . $conn->error . "</div>";
    }
}

// 4. UPDATE: Update fee status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_fee'])) {
    $fee_id = $_POST['fee_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE fees SET status=? WHERE fee_id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $fee_id);
    
    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Fee status updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating fee status: " . $conn->error . "</div>";
    }
}

// 5. CREATE: Add a new result/grade for a student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_result'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $grade = $_POST['grade'];
    $marks = $_POST['marks'];
    
    $insert_sql = "INSERT INTO results (student_id, course_id, grade, marks) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisi", $student_id, $course_id, $grade, $marks);
    
    if ($insert_stmt->execute()) {
        echo "<div class='alert alert-success'>New result added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding result: " . $conn->error . "</div>";
    }
}

// 6. DELETE: Delete a result
if (isset($_GET['delete_result'])) {
    $result_id = $_GET['delete_result'];
    
    $delete_sql = "DELETE FROM results WHERE result_id=?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $result_id);
    
    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success'>Result deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting result: " . $conn->error . "</div>";
    }
}

// Fetch courses for the current faculty
$sql = "SELECT course_id, course_name, credit FROM courses WHERE faculty_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty['faculty_id']);
$stmt->execute();
$courses = $stmt->get_result();
?>

<!-- SECTION 1: COURSE CRUD -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h4>Manage Your Courses</h4>
    </div>
    <div class="card-body">
        <!-- CREATE: Form to Add New Course -->
        <form method="POST" class="mb-4 p-3 border rounded">
            <h5>Add New Course</h5>
            <div class="row">
                <div class="col-md-5">
                    <input type="text" name="course_name" class="form-control" placeholder="Course Name" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="credit" class="form-control" placeholder="Credits" min="1" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_course" class="btn btn-success">Add Course</button>
                </div>
            </div>
        </form>

        <!-- READ: Display Courses with UPDATE/DELETE options -->
        <h5>Your Current Courses</h5>
        <table class='table table-bordered'>
            <tr>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Actions</th>
            </tr>
            <?php while($course = $courses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $course['course_name']; ?></td>
                <td><?php echo $course['credit']; ?></td>
                <td>
                    <a href="?delete_course=<?php echo $course['course_id']; ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this course? This will also delete all associated results.');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- SECTION 2: RESULTS/GRADES CRUD -->
<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h4>Manage Student Results & Grades</h4>
    </div>
    <div class="card-body">
        <!-- CREATE: Form to Add New Result -->
        <form method="POST" class="mb-4 p-3 border rounded">
            <h5>Add New Grade</h5>
            <div class="row">
                <div class="col-md-3">
                    <select name="student_id" class="form-select" required>
                        <option value="">Select Student</option>
                        <?php
                        $students_sql = "SELECT student_id, name FROM students";
                        $students_result = $conn->query($students_sql);
                        while($student = $students_result->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>"><?php echo $student['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="course_id" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php
                        $courses_sql = "SELECT course_id, course_name FROM courses WHERE faculty_id=?";
                        $courses_stmt = $conn->prepare($courses_sql);
                        $courses_stmt->bind_param("i", $faculty['faculty_id']);
                        $courses_stmt->execute();
                        $faculty_courses = $courses_stmt->get_result();
                        while($course = $faculty_courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="grade" class="form-select" required>
                        <option value="">Grade</option>
                        <option value="A">A</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B">B</option>
                        <option value="B-">B-</option>
                        <option value="C+">C+</option>
                        <option value="C">C</option>
                        <option value="F">F</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="marks" class="form-control" placeholder="Marks" min="0" max="100" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_result" class="btn btn-success">Add Grade</button>
                </div>
            </div>
        </form>

        <!-- READ: Display Results with UPDATE/DELETE options -->
        <h5>Current Results</h5>
        <?php
        $results_sql = "SELECT r.result_id, s.name as student_name, c.course_name, r.grade, r.marks 
                        FROM results r
                        JOIN students s ON r.student_id = s.student_id
                        JOIN courses c ON r.course_id = c.course_id
                        WHERE c.faculty_id = ?
                        ORDER BY c.course_name, s.name";
        $results_stmt = $conn->prepare($results_sql);
        $results_stmt->bind_param("i", $faculty['faculty_id']);
        $results_stmt->execute();
        $all_results = $results_stmt->get_result();
        ?>

        <table class='table table-bordered'>
            <tr>
                <th>Student</th>
                <th>Course</th>
                <th>Grade</th>
                <th>Marks</th>
                <th>Actions</th>
            </tr>
            <?php while($result = $all_results->fetch_assoc()): ?>
            <tr>
                <form method="POST" action="">
                    <td><?php echo $result['student_name']; ?></td>
                    <td><?php echo $result['course_name']; ?></td>
                    <td>
                        <select name="new_grade" class="form-select" required>
                            <option value="<?php echo $result['grade']; ?>" selected><?php echo $result['grade']; ?></option>
                            <option value="A">A</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B">B</option>
                            <option value="B-">B-</option>
                            <option value="C+">C+</option>
                            <option value="C">C</option>
                            <option value="F">F</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="new_marks" class="form-control" value="<?php echo $result['marks']; ?>" min="0" max="100" required>
                    </td>
                    <td>
                        <input type="hidden" name="result_id" value="<?php echo $result['result_id']; ?>">
                        <button type="submit" name="update_result" class="btn btn-warning btn-sm">Update</button>
                        <a href="?delete_result=<?php echo $result['result_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this result?');">
                           Delete
                        </a>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<!-- SECTION 3: FEE STATUS UPDATE -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h4>Update Student Fee Status</h4>
    </div>
    <div class="card-body">
        <?php
        $fees_sql = "SELECT f.fee_id, s.name as student_name, f.amount, f.status, f.due_date 
                     FROM fees f
                     JOIN students s ON f.student_id = s.student_id
                     ORDER BY s.name";
        $fees_result = $conn->query($fees_sql);
        ?>

        <table class='table table-bordered'>
            <tr>
                <th>Student</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while($fee = $fees_result->fetch_assoc()): ?>
            <tr>
                <form method="POST" action="">
                    <td><?php echo $fee['student_name']; ?></td>
                    <td>$<?php echo $fee['amount']; ?></td>
                    <td><?php echo $fee['due_date']; ?></td>
                    <td>
                        <select name="new_status" class="form-select" required>
                            <option value="<?php echo $fee['status']; ?>" selected><?php echo ucfirst($fee['status']); ?></option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="fee_id" value="<?php echo $fee['fee_id']; ?>">
                        <button type="submit" name="update_fee" class="btn btn-warning btn-sm">Update</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>