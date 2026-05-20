<?php
/**
 * includes/notifikasi_helper.php — STR/SIP expiry detection
 *
 * Parses free-form expiry text (e.g. "Kadaluarsa 2024", "Berlaku 2026-12")
 * and returns structured expiry status.
 */

/**
 * Extract date from free-form expiry text.
 * Examples: "Kadaluarsa 2024" → 2024-01-01, "Segera 2026-06" → 2026-06-01, "Berlaku 2026-12-15" → 2026-12-15
 */
function parseExpiryDate($text) {
    if (empty($text)) return null;

    // Direct date format: 2026-05-25
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $text, $m)) {
        return $m[1];
    }

    // Year-month: 2026-06
    if (preg_match('/(\d{4}-\d{2})/', $text, $m)) {
        return $m[1] . '-01';
    }

    // Year only: 2024 → Jan 1 of that year (treat as already expired if past)
    if (preg_match('/\b(20\d{2})\b/', $text, $m)) {
        return $m[1] . '-01-01';
    }

    return null;
}

/**
 * Get structured expiry info for all pegawai
 */
function getExpiryNotifications($db) {
    $pegawai = $db->query("
        SELECT id, nama_lengkap, jabatan, status_kepegawaian,
               masa_berlaku_str, masa_berlaku_sip
        FROM pegawai
        WHERE (masa_berlaku_str IS NOT NULL AND masa_berlaku_str != '' AND masa_berlaku_str != '-')
           OR (masa_berlaku_sip IS NOT NULL AND masa_berlaku_sip != '' AND masa_berlaku_sip != '-')
    ")->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];

    foreach ($pegawai as $p) {
        // Check STR
        if (!empty($p['masa_berlaku_str']) && $p['masa_berlaku_str'] !== '-') {
            $expiryDate = parseExpiryDate($p['masa_berlaku_str']);
            if ($expiryDate) {
                $days = floor((strtotime($expiryDate) - time()) / 86400);
                $severity = getExpirySeverity($days);
                if ($severity) {
                    $notifications[] = [
                        'pegawai_id' => $p['id'],
                        'nama' => $p['nama_lengkap'],
                        'jabatan' => $p['jabatan'],
                        'status_kepegawaian' => $p['status_kepegawaian'],
                        'doc' => 'STR',
                        'raw_text' => $p['masa_berlaku_str'],
                        'expiry' => $expiryDate,
                        'days' => $days,
                        'severity' => $severity,
                    ];
                }
            }
        }

        // Check SIP
        if (!empty($p['masa_berlaku_sip']) && $p['masa_berlaku_sip'] !== '-') {
            $expiryDate = parseExpiryDate($p['masa_berlaku_sip']);
            if ($expiryDate) {
                $days = floor((strtotime($expiryDate) - time()) / 86400);
                $severity = getExpirySeverity($days);
                if ($severity) {
                    $notifications[] = [
                        'pegawai_id' => $p['id'],
                        'nama' => $p['nama_lengkap'],
                        'jabatan' => $p['jabatan'],
                        'status_kepegawaian' => $p['status_kepegawaian'],
                        'doc' => 'SIP',
                        'raw_text' => $p['masa_berlaku_sip'],
                        'expiry' => $expiryDate,
                        'days' => $days,
                        'severity' => $severity,
                    ];
                }
            }
        }
    }

    // Sort by severity then days
    $severityOrder = ['expired' => 0, 'kritis' => 1, 'segera' => 2, 'peringatan' => 3];
    usort($notifications, function($a, $b) use ($severityOrder) {
        $sa = $severityOrder[$a['severity']] ?? 99;
        $sb = $severityOrder[$b['severity']] ?? 99;
        if ($sa !== $sb) return $sa - $sb;
        return $a['days'] - $b['days'];
    });

    return $notifications;
}

/**
 * Determine severity from days remaining
 */
function getExpirySeverity($days) {
    if ($days < 0) return 'expired';       // Already expired
    if ($days <= 7)  return 'kritis';      // Critical: ≤7 days
    if ($days <= 14) return 'segera';      // Urgent: ≤14 days
    if ($days <= 30) return 'peringatan';  // Warning: ≤30 days
    return null; // More than 30 days — no alert
}

/**
 * Count notifications by severity
 */
function countExpiryBySeverity($notifications) {
    $counts = ['expired' => 0, 'kritis' => 0, 'segera' => 0, 'peringatan' => 0, 'total' => 0];
    foreach ($notifications as $n) {
        $counts[$n['severity']]++;
        $counts['total']++;
    }
    return $counts;
}

/**
 * Get badge class for severity
 */
function severityBadgeClass($severity) {
    return match($severity) {
        'expired'   => 'bg-danger',
        'kritis'    => 'bg-danger',
        'segera'    => 'bg-warning text-dark',
        'peringatan' => 'bg-info',
        default     => 'bg-secondary',
    };
}

/**
 * Get severity label in Indonesian
 */
function severityLabel($severity) {
    return match($severity) {
        'expired'   => 'Kadaluarsa',
        'kritis'    => 'Kritis',
        'segera'    => 'Segera',
        'peringatan' => 'Peringatan',
        default     => $severity,
    };
}

/**
 * Get severity icon
 */
function severityIcon($severity) {
    return match($severity) {
        'expired'   => 'bi-x-circle-fill',
        'kritis'    => 'bi-exclamation-circle-fill',
        'segera'    => 'bi-exclamation-triangle-fill',
        'peringatan' => 'bi-info-circle-fill',
        default     => 'bi-info-circle-fill',
    };
}
