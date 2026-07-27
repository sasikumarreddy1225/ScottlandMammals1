<?php
require_once 'includes/db.php';

$pdo = getDbConnection();

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'common_name';

$query = "SELECT * FROM species WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND common_name LIKE ?";
    $params[] = "%$search%";
}

if ($filter) {
    $query .= " AND dietary_category = ?";
    $params[] = $filter;
}

$allowedSort = ['common_name', 'body_mass_kg'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'common_name';
}

$query .= " ORDER BY $sort";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$species = $stmt->fetchAll();

$pageTitle = "Home";

require_once 'includes/header.php';
?>

<section class="container">

    <h2 class="section-title">All Species</h2>

    <div style="
    background:white;
    padding:1.5rem;
    border-radius:8px;
    box-shadow: var(--shadow);
    margin-bottom:2rem;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
">

        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; width:100%;">

            <input
                type="text"
                name="search"
                placeholder="Search species..."
                value="<?php echo $_GET['search'] ?? ''; ?>"
                style="
            flex:1;
            padding:10px;
            border:1px solid #ccc;
            border-radius:4px;
        ">

            <?php
            $query = "SELECT DISTINCT dietary_category FROM species WHERE dietary_category IS NOT NULL ORDER BY dietary_category ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $diets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $currentFilter = $_GET['filter'] ?? '';
            ?>

            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="font-weight: bold; color: #444;">Diet:</label>
                <select name="filter" style="padding:10px; border-radius:4px;">
                    <option value="">All Diets</option>

                    <?php foreach ($diets as $row): ?>
                        <?php
                        $category = htmlspecialchars($row['dietary_category']);
                        $selected = ($currentFilter === $category) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $category; ?>" <?php echo $selected; ?>>
                            <?php echo $category; ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <select name="sort" style="padding:10px; border-radius:4px;">
                <option value="common_name">Sort by Name</option>
                <option value="body_mass_kg">Sort by Mass</option>
            </select>

            <button class="btn">Apply</button>

        </form>

    </div>

    <div class="card-grid">
        <?php foreach ($species as $sp): ?>
            <div class="card">
                <img src="<?php echo $sp['image_url'] ?: 'images/placeholder.svg'; ?>" class="card-img">

                <div class="card-body">
                    <h3><?php echo e($sp['common_name']); ?></h3>
                    <a href="species.php?key=<?php echo $sp['gbif_species_key']; ?>" class="btn">View</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>

<?php require_once 'includes/footer.php'; ?>