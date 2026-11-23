<?php
// Connessione a MySQL con PDO tramite variabili d'ambiente Docker
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'community';
$user = getenv('DB_USER') ?: 'studente';
$pass = getenv('DB_PASSWORD') ?: 'esempio_password';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$p = $_GET['p'] ?? 'home';

// Inserimento Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inserisci'])) {
    $titolo = trim($_POST['titolo'] ?? '');
    $contenuto = trim($_POST['contenuto'] ?? '');
    if ($titolo !== '' && $contenuto !== '') {
        $stmt = $pdo->prepare("INSERT INTO POST (ID_POST, TITOLO, CONTENUTO, DATA_CREAZIONE) 
                               SELECT COALESCE(MAX(ID_POST), 0) + 1, :titolo, :contenuto, CURDATE() FROM POST");
        $stmt->execute([':titolo' => $titolo, ':contenuto' => $contenuto]);
        header('Location: index.php');
        exit;
    }
}

// Eliminazione Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['elimina_id'])) {
    $stmt = $pdo->prepare("DELETE FROM POST WHERE ID_POST = :id");
    $stmt->execute([':id' => $_POST['elimina_id']]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>NVIDIA COMMUNITY</title>
</head>
<body>
    <h1>NVIDIA COMMUNITY</h1>
    
    <!-- Pulsanti di navigazione in Home Page -->
    <nav>
        <a href="index.php"><button type="button">Home</button></a>
        <a href="index.php?p=inserisci"><button type="button">Aggiungi</button></a>
    </nav>
    <hr>

    <?php if ($p === 'inserisci'): ?>
        <h2>Aggiungi Nuovo Post</h2>
        <form method="POST">
            <p><label>Titolo:<br><input type="text" name="titolo" required size="50"></label></p>
            <p><label>Contenuto:<br><textarea name="contenuto" rows="5" cols="50" required></textarea></label></p>
            <p><button type="submit" name="inserisci">Invia</button></p>
        </form>

    <?php else: ?>
        <h2>Tutti i Post</h2>
        <?php
        $posts = $pdo->query("SELECT * FROM POST ORDER BY ID_POST DESC")->fetchAll();
        if (empty($posts)): ?>
            <p>Nessun post presente.</p>
        <?php else:
            foreach ($posts as $row): ?>
            <article>
                <h3><?= htmlspecialchars($row['TITOLO']) ?></h3>
                <p><?= nl2br(htmlspecialchars($row['CONTENUTO'])) ?></p>
                <p><small>Data: <?= htmlspecialchars($row['DATA_CREAZIONE']) ?></small></p>
                <form method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo post?');">
                    <input type="hidden" name="elimina_id" value="<?= $row['ID_POST'] ?>">
                    <button type="submit">Elimina</button>
                </form>
                <hr>
            </article>
        <?php endforeach; endif; ?>
    <?php endif; ?>
</body>
</html>
