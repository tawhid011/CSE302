<?php
$user_id = $_SESSION['user_id'];

// Fetch student info
$sql = "SELECT * FROM students WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

echo "<h2>Welcome, " . $student['name'] . "</h2>";
echo "<p>Email: " . $student['email'] . "</p>";
echo "<p>Department: " . $student['department'] . "</p>";
echo "<p>Semester: " . $student['semester'] . "</p>";

// Fetch results
$sql = "SELECT c.course_name, r.grade, r.marks 
        FROM results r 
        JOIN courses c ON r.course_id = c.course_id
        WHERE r.student_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$results = $stmt->get_result();

echo "<h3>Your Results</h3>";
echo "<table class='table table-bordered'>
        <tr><th>Course</th><th>Marks</th><th>Grade</th></tr>";
while($row = $results->fetch_assoc()) {
    echo "<tr><td>{$row['course_name']}</td><td>{$row['marks']}</td><td>{$row['grade']}</td></tr>";
}
echo "</table>";

// Fetch fee info
$sql = "SELECT * FROM fees WHERE student_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student['student_id']);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();

echo "<h3>Fees Status</h3>";
echo "<p>Amount: {$fee['amount']}, Status: {$fee['status']}, Due: {$fee['due_date']}</p>";
?>
