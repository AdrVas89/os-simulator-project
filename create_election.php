<?php
require_once "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $start_date = str_replace("T", " ", $_POST["start_date"]);
    $end_date = str_replace("T", " ", $_POST["end_date"]);
    $status = $_POST["status"];

    if (empty($title) || empty($start_date) || empty($end_date)) {
        $message = "Election title, start date, and end date are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO elections (title, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $status);

        if ($stmt->execute()) {
            $message = "Election created successfully.";
        } else {
            $message = "Failed to create election.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Election - Electronic Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">Electronic Voting System</a>
        <div>
            <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card p-4">
        <h2 class="mb-4">Create Election</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Election Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date and Time</label>
                <input type="datetime-local" name="start_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">End Date and Time</label>
                <input type="datetime-local" name="end_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="closed">Closed</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Election</button>
        </form>
    </div>
</div>

</body>
</html>