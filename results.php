<?php
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$selected_election_id = isset($_GET["election_id"]) ? intval($_GET["election_id"]) : 0;

$elections = $conn->query("SELECT election_id, title FROM elections ORDER BY created_at DESC");

$results = null;
$election_title = "";
$total_votes = 0;
$winner = "No winner yet";
$turnout = 0;

if ($selected_election_id > 0) {
    $title_stmt = $conn->prepare("SELECT title FROM elections WHERE election_id = ?");
    $title_stmt->bind_param("i", $selected_election_id);
    $title_stmt->execute();
    $title_result = $title_stmt->get_result();

    if ($title_result->num_rows === 1) {
        $election_title = $title_result->fetch_assoc()["title"];
    }

    $stmt = $conn->prepare("
        SELECT 
            candidates.candidate_id,
            candidates.candidate_name,
            COUNT(votes.vote_id) AS vote_count
        FROM candidates
        LEFT JOIN votes ON candidates.candidate_id = votes.candidate_id
        WHERE candidates.election_id = ?
        GROUP BY candidates.candidate_id, candidates.candidate_name
        ORDER BY vote_count DESC
    ");
    $stmt->bind_param("i", $selected_election_id);
    $stmt->execute();
    $results = $stmt->get_result();

    $total_stmt = $conn->prepare("SELECT COUNT(*) AS total_votes FROM votes WHERE election_id = ?");
    $total_stmt->bind_param("i", $selected_election_id);
    $total_stmt->execute();
    $total_votes_result = $total_stmt->get_result();
    $total_votes = $total_votes_result->fetch_assoc()["total_votes"];

    $voter_count_result = $conn->query("SELECT COUNT(*) AS total_voters FROM users WHERE role = 'voter' AND status = 'active'");
    $total_voters = $voter_count_result->fetch_assoc()["total_voters"];

    if ($total_voters > 0) {
        $turnout = round(($total_votes / $total_voters) * 100, 2);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Results - Electronic Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Electronic Voting System</a>
        <div>
            <?php if ($_SESSION["role"] === "admin"): ?>
                <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <?php else: ?>
                <a href="voter_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card p-4 mb-4">
        <h2 class="mb-4">Election Results</h2>

        <form method="GET" action="">
            <div class="mb-3">
                <label class="form-label">Select Election</label>
                <select name="election_id" class="form-control" required>
                    <option value="">-- Select Election --</option>
                    <?php while ($election = $elections->fetch_assoc()): ?>
                        <option value="<?php echo $election["election_id"]; ?>" 
                            <?php if ($selected_election_id == $election["election_id"]) echo "selected"; ?>>
                            <?php echo htmlspecialchars($election["title"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">View Results</button>
        </form>
    </div>

    <?php if ($selected_election_id > 0): ?>
        <div class="card p-4">
            <h3 class="mb-3"><?php echo htmlspecialchars($election_title); ?></h3>

            <p><strong>Total Votes:</strong> <?php echo $total_votes; ?></p>
            <p><strong>Voter Turnout:</strong> <?php echo $turnout; ?>%</p>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Candidate / Option</th>
                        <th>Votes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $top_votes = -1;
                    $winner_names = [];

                    if ($results && $results->num_rows > 0): 
                        while ($row = $results->fetch_assoc()):
                            if ($row["vote_count"] > $top_votes) {
                                $top_votes = $row["vote_count"];
                                $winner_names = [$row["candidate_name"]];
                            } elseif ($row["vote_count"] == $top_votes) {
                                $winner_names[] = $row["candidate_name"];
                            }
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["candidate_name"]); ?></td>
                                <td><?php echo $row["vote_count"]; ?></td>
                            </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="2">No candidates found for this election.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            if ($top_votes > 0) {
                $winner = implode(", ", $winner_names);
            }
            ?>

            <div class="alert alert-success">
                <strong>Winner:</strong> <?php echo htmlspecialchars($winner); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>