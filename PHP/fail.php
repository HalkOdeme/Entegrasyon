<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşlem Sonucu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>

    <!-- Navbar -->
    <?php include 'nav.php'; ?>

    <!-- İçerik Alanı -->
    <div class="container mt-5">
        <h1>İşlem Başarısız!</h1>

        <div class="result-item">
            <span>order_no:</span> <?php echo htmlspecialchars($_GET['order_no']); ?>
        </div>
        <div class="result-item">
            <span>order_id:</span> <?php echo htmlspecialchars($_GET['order_id']); ?>
        </div>
        <div class="result-item">
            <span>invoice_id:</span> <?php echo htmlspecialchars($_GET['invoice_id']); ?>
        </div>
        <div class="result-item">
            <span>status_code:</span> <?php echo htmlspecialchars($_GET['status_code']); ?>
        </div>
        <div class="result-item">
            <span>transaction_type:</span> <?php echo htmlspecialchars($_GET['transaction_type']); ?>
        </div>
        <div class="result-item">
            <span>payment_status:</span> <?php echo htmlspecialchars($_GET['payment_status']); ?>
        </div>
        <div class="result-item">
            <span>md_status:</span> <?php echo htmlspecialchars($_GET['md_status']); ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        
    <?php include 'footer.php'; ?>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>