<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';

$currentUser = requireAuth('login.php');

$db = Database::getInstance()->getConnection();
$config = Config::getInstance();
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);

$itemId = $_GET['id'] ?? 0;

if ($itemId) {
    $result = $portfolioManager->deleteItem($itemId, $currentUser->id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Portfolio item deleted successfully!';
    } else {
        $_SESSION['error_message'] = $result['error'] ?? 'Failed to delete portfolio item';
    }
}

header('Location: dashboard.php');
exit;
