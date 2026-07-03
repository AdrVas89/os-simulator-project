<?php
require_once "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Electronic Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">Electronic Voting System</a>
        <div>
            <span class="text-white me-3">Welcome, <?php echo $_SESSION["name"]; ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card p-4">
                <h4>Create Election</h4>
                <p>Create a new election with title, description, dates, and status.</p>
                <a href="create_election.php" class="btn btn-primary">Create Election</a>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4">
                <h4>Add Candidates</h4>
                <p>Add candidates or voting options to an existing election.</p>
                <a href="add_candidate.php" class="btn btn-primary">Add Candidate</a>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4">
                <h4>View Results</h4>
                <p>View vote totals, winner, and election summary.</p>
                <a href="results.php" class="btn btn-primary">View Results</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>