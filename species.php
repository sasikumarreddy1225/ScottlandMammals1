<?php
require_once 'includes/db.php';

if (!function_exists('e')) {
    function e($text)
    {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($_GET['key']) || !is_numeric($_GET['key'])) {
    header('Location: index.php');
    exit;
}

$speciesKey = (int)$_GET['key'];
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT * FROM species WHERE gbif_species_key = ?");
$stmt->execute([$speciesKey]);
$species = $stmt->fetch();

if (!$species) {
    header('Location: index.php');
    exit;
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$dateFilter = "";
$params = [$speciesKey];

if ($start && $end) {
    $dateFilter = " AND observation_date BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
}

$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM observations WHERE gbif_species_key = ? $dateFilter");
$countStmt->execute($params);
$totalObservations = $countStmt->fetchColumn();
$totalPages = ceil($totalObservations / $limit);

$stmt = $pdo->prepare("SELECT locality, individual_count, latitude, longitude, observation_date 
                       FROM observations WHERE gbif_species_key = ? $dateFilter 
                       LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$observations = $stmt->fetchAll();

$mapStmt = $pdo->prepare("SELECT latitude, longitude, locality, individual_count FROM observations WHERE gbif_species_key = ?");
$mapStmt->execute([$speciesKey]);
$allObservations = $mapStmt->fetchAll();

$pageTitle = $species['common_name'];
require_once 'includes/header.php';
?>

<div class="details-header" style="
    height: 400px;
    display: flex;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), 
                url('<?php echo $species['image_url'] ? e($species['image_url']) : 'images/placeholder.svg'; ?>') center/cover no-repeat;
">
    <div>
        <h1 style="font-size: 3rem; margin-bottom: 0;"><?php echo e($species['common_name']); ?></h1>
        <p style="font-style: italic; font-size: 1.2rem; opacity: 0.9;"><?php echo e($species['species_name']); ?></p>
    </div>
</div>

<section class="container" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px;">

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">

        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
            <h3 style="margin-top: 0;">Update Species Image</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" name="key" value="<?php echo $speciesKey; ?>">
                <input type="file" name="image" required style="padding: 5px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                <button class="btn">Upload Image</button>
            </form>
        </div>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 50px;">
        <div style="padding: 20px; background: white; border: 1px solid #eee; border-radius: 8px; text-align: center;">
            <h4 style="color: #666; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px;">Conservation</h4>
            <p style="font-weight: bold; font-size: 1.1rem;"><?php echo $species['iucn_red_list_category'] ?: 'Unknown'; ?></p>
        </div>
        <div style="padding: 20px; background: white; border: 1px solid #eee; border-radius: 8px; text-align: center;">
            <h4 style="color: #666; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px;">Habitat</h4>
            <p style="font-weight: bold; font-size: 1.1rem;"><?php echo e($species['habitat']); ?></p>
        </div>
        <div style="padding: 20px; background: white; border: 1px solid #eee; border-radius: 8px; text-align: center;">
            <h4 style="color: #666; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px;">Diet</h4>
            <p style="font-weight: bold; font-size: 1.1rem;"><?php echo e($species['dietary_category']); ?></p>
        </div>
        <div style="padding: 20px; background: white; border: 1px solid #eee; border-radius: 8px; text-align: center;">
            <h4 style="color: #666; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px;">Body Mass</h4>
            <p style="font-weight: bold; font-size: 1.1rem;"><?php echo e($species['body_mass_kg']); ?> kg</p>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 40px 0;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Observations</h2>

        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="key" value="<?php echo $speciesKey; ?>">
            <input type="date" name="start" value="<?php echo e($start); ?>" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
            <span style="color: #999;">to</span>
            <input type="date" name="end" value="<?php echo e($end); ?>" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
            <button class="btn" style="padding: 6px 15px;">Filter</button>
        </form>
    </div>

    <?php if (empty($observations)): ?>
        <p style="padding: 40px; text-align: center; background: #fdfdfd; border: 1px dashed #ccc; border-radius: 8px;">No sightings found for this period.</p>
    <?php else: ?>
        <div class="card-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($observations as $obs): ?>
                <div class="card" style="border: 1px solid #eee; padding: 15px; border-radius: 8px; background: white;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <span style="font-weight: bold; color: #2e7d32;"><i class="fa-solid fa-location-dot"></i> <?php echo $obs['locality'] ? e($obs['locality']) : 'Scottish Highlands'; ?></span>
                        <span style="color: #666; font-size: 0.9rem;"><i class="fa-solid fa-calendar"></i> <?php echo e($obs['observation_date']); ?></span>
                        <span style="color: #444;"><i class="fa-solid fa-calculator"></i> <strong><?php echo e($obs['individual_count']); ?></strong> observed</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: center; gap: 10px;">
            <?php if ($page > 1): ?>
                <a class="btn btn-outline" href="?key=<?php echo $speciesKey; ?>&page=<?php echo $page - 1; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>">← Previous</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a class="btn" href="?key=<?php echo $speciesKey; ?>&page=<?php echo $page + 1; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>">Next →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <div>
            <h3 class="section-title" style="text-align:left;">Local Distribution</h3>
            <div id="map" style="height: 400px; border-radius: 8px; border: 1px solid #eee;"></div>
        </div>
        <div>
            <h3 class="section-title" style="text-align:left;">Observation Frequency</h3>
            <canvas id="chart" style="max-height: 400px;"></canvas>
        </div>
    </div>

</section>

<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var observations = <?php echo json_encode($allObservations); ?>;

        var map = L.map('map').setView([57, -4], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        observations.forEach(obs => {
            if (obs.latitude && obs.longitude) {
                L.marker([obs.latitude, obs.longitude])
                    .addTo(map)
                    .bindPopup("<b>" + (obs.locality || "Sighting") + "</b><br>Count: " + obs.individual_count);
            }
        });

        let counts = {};
        observations.forEach(o => {
            let key = o.locality || "Unknown";
            counts[key] = (counts[key] || 0) + 1;
        });

        new Chart(document.getElementById("chart"), {
            type: 'bar',
            data: {
                labels: Object.keys(counts),
                datasets: [{
                    label: 'Number of Sightings',
                    data: Object.values(counts),
                    backgroundColor: '#2e7d32'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>