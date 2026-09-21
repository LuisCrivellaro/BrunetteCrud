<?php
declare(strict_types=1);

/**
 * Conexão SQLite. O arquivo e as tabelas são criados automaticamente
 * na primeira requisição, junto com o admin padrão e dados de exemplo.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = dirname(DB_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $isNew = !file_exists(DB_FILE);

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    migrate($pdo);
    seed_admin($pdo);
    if ($isNew) {
        seed_demo($pdo);
    }

    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS admins (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            email         TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS establishments (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT NOT NULL,
            type          TEXT NOT NULL,
            neighborhood  TEXT NOT NULL,
            address       TEXT,
            opening_hours TEXT,
            instagram     TEXT,
            description   TEXT,
            image         TEXT,
            created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS events (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            establishment_id INTEGER NOT NULL REFERENCES establishments(id) ON DELETE CASCADE,
            title            TEXT NOT NULL,
            description      TEXT,
            event_date       TEXT NOT NULL,
            start_time       TEXT,
            price            TEXT,
            genre            TEXT,
            image            TEXT,
            created_at       TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_events_date  ON events(event_date);
        CREATE INDEX IF NOT EXISTS idx_events_place ON events(establishment_id);
    ');
}

function seed_admin(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)');
    $stmt->execute([ADMIN_DEFAULT_EMAIL, password_hash(ADMIN_DEFAULT_PASSWORD, PASSWORD_DEFAULT)]);
}

/**
 * Dados fictícios só para o site não nascer vazio. Podem ser apagados no painel.
 */
function seed_demo(PDO $pdo): void
{
    $places = [
        ['Porão do Trajano', 'bar', 'Centro', 'Rua Trajano Reis, 100', 'Ter a dom, 18h às 2h', '@poraodotrajano',
         'Bar de porão com chope artesanal, petiscos e rodas de samba no fim de semana.'],
        ['Batel Lounge', 'balada', 'Batel', 'Av. do Batel, 1000', 'Qui a sáb, 22h às 5h', '@batellounge',
         'Pista, camarotes e DJs residentes. Dress code casual chic.'],
        ['Bar do Largo', 'bar', 'São Francisco', 'Largo da Ordem, 50', 'Todos os dias, 17h às 1h', '@bardolargo',
         'Mesas na calçada, música ao vivo e o clima boêmio do Largo da Ordem.'],
        ['Clube Jardim', 'festa', 'Jardim Botânico', 'Rua Ubaldino do Amaral, 200', 'Eventos sazonais', '@clubejardim',
         'Festas ao ar livre em meio ao verde, sempre com line-up especial.'],
        ['Santa Rock House', 'casa-de-show', 'Santa Felicidade', 'Av. Manoel Ribas, 5000', 'Sex e sáb, 20h às 3h', '@santarockhouse',
         'Casa de show com palco amplo, bandas autorais e covers.'],
        ['Pub Água Verde', 'pub', 'Água Verde', 'Av. Água Verde, 800', 'Seg a sáb, 18h às 0h', '@pubaguaverde',
         'Pub aconchegante com cervejas importadas e noites de trivia.'],
    ];

    $insPlace = $pdo->prepare(
        'INSERT INTO establishments (name, type, neighborhood, address, opening_hours, instagram, description, image)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    // Fotos em uploads/ (nome do local => arquivo)
    $placeImages = [
        'Porão do Trajano' => 'porao-do-trajano.jpg',
        'Bar do Largo'     => 'bar-do-largo.jpg',
    ];
    foreach ($places as $p) {
        $p[] = $placeImages[$p[0]] ?? null;
        $insPlace->execute($p);
    }

    $d = static fn (int $n): string => date('Y-m-d', strtotime("+$n days"));

    $events = [
        [1, 'Roda de Samba do Porão',  'Samba de raiz com convidados e chope em dobro até as 21h.', $d(0), '19:00', 'R$ 20',  'Samba'],
        [2, 'Batel Sessions',          'Noite de house e techno com line-up de DJs locais.',        $d(1), '23:00', 'R$ 60',  'Eletrônica'],
        [3, 'Sexta no Largo',          'Música ao vivo e happy hour estendido.',                    $d(2), '18:00', 'Grátis', 'MPB / Pop'],
        [5, 'Noite do Rock Nacional',  'Três bandas cobrindo os clássicos do rock brasileiro.',     $d(3), '21:00', 'R$ 35',  'Rock'],
        [4, 'Sunset no Jardim',        'Festa ao ar livre do pôr do sol até a madrugada.',          $d(5), '16:00', 'R$ 80',  'Eletrônica'],
        [6, 'Trivia Night',            'Quiz de cultura pop em times de até 5 pessoas.',            $d(6), '20:00', 'Grátis', 'Jogos'],
        [2, 'Baile Funk Batel',        'A noite mais quente da semana, com os maiores hits.',       $d(8), '23:30', 'R$ 50',  'Funk'],
        [3, 'Pagode do Largo',         'Pagode e cerveja gelada na calçada mais famosa da cidade.', $d(10), '17:00', 'R$ 15', 'Pagode'],
    ];

    $insEvent = $pdo->prepare(
        'INSERT INTO events (establishment_id, title, description, event_date, start_time, price, genre, image)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    // Fotos em uploads/ (título do evento => arquivo)
    $eventImages = [
        'Roda de Samba do Porão' => 'roda-de-samba-no-porao.jpg',
        'Batel Sessions'         => 'batel-sessions.jpg',
        'Sexta no Largo'         => 'sexta-no-largo.jpg',
        'Noite do Rock Nacional' => 'noite-de-rock-nacional.jpg',
        'Sunset no Jardim'       => 'sunset-no-jardim.jpg',
        'Trivia Night'           => 'trivia-night.jpg',
        'Pagode do Largo'        => 'pagode-do-largo.jpg',
    ];
    foreach ($events as $e) {
        $e[] = $eventImages[$e[1]] ?? null;
        $insEvent->execute($e);
    }
}
