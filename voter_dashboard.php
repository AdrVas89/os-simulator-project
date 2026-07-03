<?php
require_once "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "voter") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT 
        elections.election_id,
        elections.title,
        elections.description,
        elections.start_date,
        elections.end_date,
        elections.status,
        (
            SELECT COUNT(*) 
            FROM votes 
            WHERE votes.election_id = elections.election_id 
            AND votes.voter_id = ?
        ) AS has_voted
    FROM elections
    WHERE elections.status = 'active'
    ORDER BY elections.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$elections = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voter Dashboard - Electronic Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="voter_dashboard.php">Electronic Voting System</a>
        <div>
            <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
            <a href="results.php" class="btn btn-outline-light btn-sm">View Results</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Voter Dashboard</h2>

    <div class="card p-4">
        <h3 class="mb-3">Active Elections</h3>

        <?php if ($elections->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Election Title</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($election = $elections->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($election["title"]); ?></td>
                            <td><?php echo htmlspecialchars($election["description"]); ?></td>
                            <td><?php echo htmlspecialchars($election["start_date"]); ?></td>
                            <td><?php echo htmlspecialchars($election["end_date"]); ?></td>
                            <td>
                                <?php if ($election["has_voted"] > 0): ?>
                                    <span class="badge bg-success">Already Voted</span>
                                    <br><br>
                                    <a href="results.php?election_id=<?php echo $election["election_id"]; ?>" class="btn btn-sm btn-secondary">
                                        View Results
                                    </a>
                                <?php else: ?>
                                    <a href="vote.php?election_id=<?php echo $election["election_id"]; ?>" class="btn btn-sm btn-primary">
                                        Vote Now
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                No active elections are available at the moment.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>