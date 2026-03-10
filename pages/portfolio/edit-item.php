<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

$currentUser = requireAuth('login.php');
$csrf = new CSRFProtection();

$db = Database::getInstance()->getConnection();
$config = Config::getInstance();
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);

$itemId = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Get the item
$items = $portfolioManager->getItems($currentUser->id);
$item = null;
foreach ($items as $i) {
    if ($i['id'] == $itemId) {
        $item = $i;
        break;
    }
}

if (!$item) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $itemType = $_POST['type'] ?? 'project';
        $itemDate = $_POST['item_date'] ?? null;
        $tags = !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [];
        $links = !empty($_POST['links']) ? explode(',', $_POST['links']) : [];
        
        $tags = array_map('trim', $tags);
        $links = array_map('trim', $links);
        
        $data = [
            'type' => $itemType,
            'title' => $title,
            'description' => $description,
            'item_date' => $itemDate,
            'tags' => $tags,
            'links' => $links
        ];
        
        $result = $portfolioManager->updateItem($itemId, $currentUser->id, $data, $_FILES);
        
        if ($result['success']) {
            $success = 'Portfolio item updated successfully!';
            header('refresh:2;url=../dashboard/dashboard.php');
        } else {
            $error = $result['error'] ?? 'Failed to update portfolio item';
        }
    }
}
?>
<?php include "../../includes/header.php"; ?>
<link rel="stylesheet" href="../../css/dashboard.css">
<link rel="stylesheet" href="../../css/forms.css">
<style>
    body, html {
        overflow-x: hidden;
    }
    .dashboard-container {
        max-width: none !important;
        width: 100% !important;
        padding: 60px 10% !important;
    }
    .form-container {
        max-width: none !important;
        width: 100% !important;
        margin: 0 !important;
    }
    .form-group {
        width: 100% !important;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100% !important;
        box-sizing: border-box !important;
    }
</style>

<body>
<div class="wrapper">
<?php include "../../includes/nav-dashboard.php"; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Edit <?php echo ucfirst($item['type']); ?></h1>
        <p>Update your portfolio item</p>
    </div>
    
    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="portfolio-form">
            <?php echo $csrf->getTokenField(); ?>
            
            <div class="form-group">
                <label for="type">Item Type</label>
                <select id="type" name="type" required>
                    <option value="project" <?php echo $item['type'] === 'project' ? 'selected' : ''; ?>>Project</option>
                    <option value="achievement" <?php echo $item['type'] === 'achievement' ? 'selected' : ''; ?>>Achievement</option>
                    <option value="milestone" <?php echo $item['type'] === 'milestone' ? 'selected' : ''; ?>>Milestone</option>
                    <option value="skill" <?php echo $item['type'] === 'skill' ? 'selected' : ''; ?>>Skill</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($item['title']); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="item_date">Date</label>
                <input type="date" id="item_date" name="item_date" value="<?php echo $item['date'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="tags">Tags (comma-separated)</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars(implode(', ', $item['tags'] ?? [])); ?>">
            </div>
            
            <div class="form-group">
                <label for="links">Links (comma-separated)</label>
                <input type="text" id="links" name="links" value="<?php echo htmlspecialchars(implode(', ', $item['links'] ?? [])); ?>">
            </div>
            
            <div class="form-group">
                <label for="files">Upload New Files (optional)</label>
                <input type="file" id="files" name="files[]" multiple>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Item
                </button>
                <a href="../dashboard/dashboard.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</div>
<?php include "../../includes/footer.php"; ?>
</body>
</html>
