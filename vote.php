<?php
require_once "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "voter") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$success = false;

$election_id = 0;

if (isset($_GET["election_id"])) {
    $election_id = intval($_GET["election_id"]);
}

if (isset($_POST["election_id"])) {
    $election_id = intval($_POST["election_id"]);
}

if ($election_id <= 0) {
    header("Location: voter_dashboard.php");
    exit();
}

$election_stmt = $conn->prepare("SELECT * FROM elections WHERE election_id = ? AND status = 'active'");
$election_stmt->bind_param("i", $election_id);
$election_stmt->execute();
$election_result = $election_stmt->get_result();

if ($election_result->num_rows !== 1) {
    die("Election not found or not active.");
}

$election = $election_result->fetch_assoc();

$check_vote = $conn->prepare("SELECT vote_id FROM votes WHERE election_id = ? AND voter_id = ?");
$check_vote->bind_param("ii", $election_id, $user_id);
$check_vote->execute();
$vote_check_result = $check_vote->get_result();

$already_voted = $vote_check_result->num_rows > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_voted) {
    $candidate_id = intval($_POST["candidate_id"]);

    if ($candidate_id <= 0) {
        $message = "Please select a candidate.";
    } else {
        $candidate_check = $conn->prepare("
            SELECT candidate_id 
            FROM candidates 
            WHERE candidate_id = ? AND election_id = ?
        ");
        $candidate_check->bind_param("ii", $candidate_id, $election_id);
        $candidate_check->execute();
        $candidate_result = $candidate_check->get_result();

        if ($candidate_result->num_rows !== 1) {
            $message = "Invalid candidate selected.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO votes (election_id, candidate_id, voter_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iii", $election_id, $candidate_id, $user_id);

            if ($stmt->execute()) {
                $message = "Your vote has been submitted successfully.";
                $success = true;
                $already_voted = true;
            } else {
                $message = "You have already voted in this election.";
            }
        }
    }
}

$candidates_stmt = $conn->prepare("
    SELECT candidate_id, candidate_name, description 
    FROM candidates 
    WHERE election_id = ?
    ORDER BY candidate_name ASC
");
$candidates_stmt->bind_param("i", $election_id);
$candidates_stmt->execute();
$candidates = $candidates_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vote - Electronic Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="voter_dashboard.php">Electronic Voting System</a>
        <div>
            <a href="voter_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card p-4">
        <h2 class="mb-3"><?php echo htmlspecialchars($election["title"]); ?></h2>

        <p><?php echo htmlspecialchars($election["description"]); ?></p>

        <p>
            <strong>Voting Period:</strong>
            <?php echo htmlspecialchars($election["start_date"]); ?>
            to
            <?php echo htmlspecialchars($election["end_date"]); ?>
        </p>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-warning'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($already_voted): ?>
            <div class="alert alert-info">
                You have already voted in this election. Duplicate voting is not allowed.
            </div>

            <a href="results.php?election_id=<?php echo $election_id; ?>" class="btn btn-primary">
                View Results
            </a>

            <a href="voter_dashboard.php" class="btn btn-secondary">
                Back to Dashboard
            </a>

        <?php else: ?>

            <?php if ($candidates->num_rows > 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">

                    <h4 class="mb-3">Select Your Candidate / Option</h4>

                    <?php while ($candidate = $candidates->fetch_assoc()): ?>
                        <div class="card p-3 mb-3">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="candidate_id" 
                                    value="<?php echo $candidate["candidate_id"]; ?>" 
                                    required
                                >

                                <label class="form-check-label">
                                    <strong><?php echo htmlspecialchars($candidate["candidate_name"]); ?></strong>
                                    <br>
                                    <?php echo htmlspecialchars($candidate["description"]); ?>
                                </label>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <button type="submit" class="btn btn-primary">
                        Submit Vote
                    </button>

                    <a href="voter_dashboard.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    No candidates have been added to this election yet.
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

</body>
</html>