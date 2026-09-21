<?php
declare(strict_types=1);

/**
 * Filtros aceitos: upcoming (bool), q, type, neighborhood, when (hoje|semana|mes),
 * establishment_id, order (asc|desc).
 */
function fetch_events(array $f = [], ?int $limit = null): array
{
    $sql = 'SELECT ev.*, es.name AS place_name, es.type AS place_type,
                   es.neighborhood AS place_neighborhood, es.image AS place_image
            FROM events ev
            JOIN establishments es ON es.id = ev.establishment_id
            WHERE 1=1';
    $params = [];

    if (!empty($f['upcoming'])) {
        $sql .= ' AND ev.event_date >= :today';
        $params[':today'] = today();
    }
    if (!empty($f['q'])) {
        $sql .= " AND (ev.title LIKE :q ESCAPE '\\' OR es.name LIKE :q ESCAPE '\\' OR ev.genre LIKE :q ESCAPE '\\')";
        $params[':q'] = like_escape($f['q']);
    }
    if (!empty($f['type'])) {
        $sql .= ' AND es.type = :type';
        $params[':type'] = $f['type'];
    }
    if (!empty($f['neighborhood'])) {
        $sql .= ' AND es.neighborhood = :nb';
        $params[':nb'] = $f['neighborhood'];
    }
    if (!empty($f['establishment_id'])) {
        $sql .= ' AND ev.establishment_id = :eid';
        $params[':eid'] = (int) $f['establishment_id'];
    }
    if (!empty($f['when'])) {
        switch ($f['when']) {
            case 'hoje':
                $sql .= ' AND ev.event_date = :d1';
                $params[':d1'] = today();
                break;
            case 'semana':
                $sql .= ' AND ev.event_date BETWEEN :d1 AND :d2';
                $params[':d1'] = today();
                $params[':d2'] = date('Y-m-d', strtotime('+7 days'));
                break;
            case 'mes':
                $sql .= ' AND ev.event_date BETWEEN :d1 AND :d2';
                $params[':d1'] = today();
                $params[':d2'] = date('Y-m-d', strtotime('+30 days'));
                break;
        }
    }

    $dir = ($f['order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY ev.event_date $dir, ev.start_time $dir";
    if ($limit) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_event(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT ev.*, es.name AS place_name, es.type AS place_type, es.neighborhood AS place_neighborhood,
                es.address AS place_address, es.image AS place_image
         FROM events ev JOIN establishments es ON es.id = ev.establishment_id
         WHERE ev.id = ?'
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Filtros aceitos: q, type, neighborhood. */
function fetch_establishments(array $f = []): array
{
    $sql = 'SELECT es.*,
                   (SELECT COUNT(*) FROM events ev
                     WHERE ev.establishment_id = es.id AND ev.event_date >= :today) AS upcoming_count
            FROM establishments es WHERE 1=1';
    $params = [':today' => today()];

    if (!empty($f['q'])) {
        $sql .= " AND (es.name LIKE :q ESCAPE '\\' OR es.neighborhood LIKE :q ESCAPE '\\')";
        $params[':q'] = like_escape($f['q']);
    }
    if (!empty($f['type'])) {
        $sql .= ' AND es.type = :type';
        $params[':type'] = $f['type'];
    }
    if (!empty($f['neighborhood'])) {
        $sql .= ' AND es.neighborhood = :nb';
        $params[':nb'] = $f['neighborhood'];
    }
    $sql .= ' ORDER BY es.name COLLATE NOCASE';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_establishment(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM establishments WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function fetch_neighborhoods(): array
{
    return db()->query('SELECT DISTINCT neighborhood FROM establishments ORDER BY neighborhood COLLATE NOCASE')
        ->fetchAll(PDO::FETCH_COLUMN);
}
