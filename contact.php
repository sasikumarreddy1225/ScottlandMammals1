<?php
$pageTitle = "Contact";
$message = "";
$messageClass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $species = htmlspecialchars($_POST['species'] ?? '');
    $details = htmlspecialchars($_POST['details'] ?? '');

    if ($name && $species && $details) {
        $message = "Thank you! Your submission has been received successfully.";
        $messageClass = "border-left: 4px solid #2e7d32; background: #f1f8e9; color: #1b5e20;";
    } else {
        $message = "Please fill in all fields before submitting.";
        $messageClass = "border-left: 4px solid #d32f2f; background: #ffebee; color: #b71c1c;";
    }
}

require_once 'includes/header.php';
?>

<section class="container" style="max-width: 650px; margin: 0 auto; padding: 40px 20px; line-height: 1.6;">
    <h2 class="section-title" style="margin-bottom: 10px;">Contact Us</h2>
    <p style="margin-bottom: 30px; color: #555;">
        Have you spotted something rare? Use the form below to share your sightings with our conservation team.
    </p>

    <?php if ($message): ?>
        <div style="padding: 15px; margin-bottom: 25px; border-radius: 4px; <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="contact-form">
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px;">Your Name</label>
                <input type="text" name="name" id="name" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px;">Species Sighted</label>
                <input type="text" name="species" id="species" placeholder="e.g. Red Squirrel" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px;">Details & Location</label>
                <textarea name="details" id="details" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <button class="btn" style="padding: 12px 30px; cursor: pointer; font-weight: bold;">Submit Sighting</button>
        </form>
    </div>
</section>

<script src="js/validation.js"></script>

<?php require_once 'includes/footer.php'; ?>