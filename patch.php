<?php
// Add this at the top of your voting form processing
$electionStatus = $db->query("SELECT * FROM election_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$now = new DateTime();
$startTime = new DateTime($electionStatus['election_start']);
$endTime = new DateTime($electionStatus['election_end']);

if ($now < $startTime || $now > $endTime) {
    die("Voting is not currently allowed");
}
?>


<!-- in the voter homepage -->