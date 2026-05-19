<?php
// sidebar.php — Backward compatibility redirect to layout
// Deprecated: use includes/layout.php instead
if (!isset($pageTitle)) $pageTitle = 'SIM Kepegawaian';
if (!isset($activePage)) $activePage = '';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/layout.php';
