<?php
// ============================================
// LearnovaX - Shared Header
// includes/header.php
// ============================================
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' – LearnovaX' : 'LearnovaX'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
