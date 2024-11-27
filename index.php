<?php
// Set the Content-Type to HTML
header('Content-Type: text/html');

// Helper function to fetch DNS records
function getDNSRecords($type, $domain) {
    $records = dns_get_record($domain, DNS_TXT);
    foreach ($records as $record) {
        if (isset($record['txt']) && stripos($record['txt'], $type) !== false) {
            return $record['txt'];
        }
    }
    return "No $type record found.";
}

// Check if domain is provided
$domain = isset($_GET['domain']) ? htmlspecialchars($_GET['domain']) : null;

if (!$domain) {
    $error = "Please provide a domain name as a query parameter. Example: ?domain=example.com";
    $spf = $dmarc = $dkim = null;
} else {
    // Fetch records
    $spf = getDNSRecords('v=spf1', $domain);
    $dmarc = getDNSRecords('v=DMARC1', '_dmarc.' . $domain);
    $dkimSelector = 'default'; // Update this to match the DKIM selector used
    $dkim = getDNSRecords('v=DKIM1', $dkimSelector . '._domainkey.' . $domain);
    $error = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS Configuration Checker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.4em 0.6em;
        }
        .ok {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <h1>DNS Configuration Checker</h1>
        <p class="text-muted">Check SPF, DKIM, and DMARC records for your domain</p>
    </div>

    <div class="card p-4">
        <form class="row g-3" method="get" action="">
            <div class="col-md-9">
                <input type="text" name="domain" class="form-control" placeholder="Enter your domain (e.g., example.com)" value="<?= $domain ?? '' ?>" required>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary w-100">Check Records</button>
            </div>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger mt-4" role="alert">
            <?= $error ?>
        </div>
    <?php else: ?>
        <?php if ($domain): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="card-title text-center">Results for: <strong><?= $domain ?></strong></h3>
                    <table class="table table-bordered mt-4">
                        <thead class="table-light">
                            <tr>
                                <th>Record Type</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SPF</td>
                                <td>
                                    <span class="status-badge <?= strpos($spf, 'No') === false ? 'ok' : 'error' ?>">
                                        <?= strpos($spf, 'No') === false ? 'Found' : 'Not Found' ?>
                                    </span>
                                </td>
                                <td><?= $spf ?></td>
                            </tr>
                            <tr>
                                <td>DMARC</td>
                                <td>
                                    <span class="status-badge <?= strpos($dmarc, 'No') === false ? 'ok' : 'error' ?>">
                                        <?= strpos($dmarc, 'No') === false ? 'Found' : 'Not Found' ?>
                                    </span>
                                </td>
                                <td><?= $dmarc ?></td>
                            </tr>
                            <tr>
                                <td>DKIM (Selector: <?= $dkimSelector ?>)</td>
                                <td>
                                    <span class="status-badge <?= strpos($dkim, 'No') === false ? 'ok' : 'error' ?>">
                                        <?= strpos($dkim, 'No') === false ? 'Found' : 'Not Found' ?>
                                    </span>
                                </td>
                                <td><?= $dkim ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
