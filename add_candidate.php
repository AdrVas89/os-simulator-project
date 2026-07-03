<?php
require_once "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

$elections = $conn->query("SELECT election_id, title FROM elections ORDER BY created_at DESC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $election_id = $_POST["election_id"];
    $candidate_name = trim($_POST["candidate_name"]);
    $description = trim($_POST["description"]);

    if (empty($election_id) || empty($candidate_name)) {
        $message = "Election and candidate name are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO candidates (election_id, candidate_name, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $election_id, $candidate_name, $description);

        if ($stmt->execute()) {
            $message = "Candidate added successfully.";
        } else {
            $message = "Failed to add candidate.";
        }
    }
}

$candidate_list = $conn->query("
    SELECT candidates.candidate_name, candidates.description, elections.title 
    FROM candidates
    INNER JOIN elections ON candidates.election_id = elections.election_id
    ORDER BY elections.created_at DESC, candidates.candidate_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Candidate - Electronic Voting System</title>
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
    <div class="card p-4 mb-4">
        <h2 class="mb-4">Add Candidate / Voting Option</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Select Election</label>
                <select name="election_id" class="form-control" required>
                    <option value="">-- Select Election --</option>
                    <?php while ($election = $elections->fetch_assoc()): ?>
                        <option value="<?php echo $election["election_id"]; ?>">
                            <?php echo htmlspecialchars($election["title"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Candidate / Option Name</label>
                <input type="text" name="candidate_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Add Candidate</button>
        </form>
    </div>

    <div class="card p-4">
        <h3 class="mb-3">Existing Candidates</h3>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Election</th>
                    <th>Candidate / Option</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($candidate_list->num_rows > 0): ?>
                    <?php while ($candidate = $candidate_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate["title"]); ?></td>
                            <td><?php echo htmlspecialchars($candidate["candidate_name"]); ?></td>
                            <td><?php echo htmlspecialchars($candidate["description"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No candidates added yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>