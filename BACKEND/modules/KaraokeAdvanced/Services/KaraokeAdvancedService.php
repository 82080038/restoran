<?php

namespace App\Modules\KaraokeAdvanced\Services;

use App\Core\Database;
use PDO;

class KaraokeAdvancedService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->connect();
    }

    // ==================== SONG CATALOG ====================

    public function getSongs($tenantId, $search = null, $genre = null, $language = null, $limit = 100, $offset = 0)
    {
        $sql = "SELECT * FROM karaoke_song_catalog WHERE tenant_id = :tenant_id AND is_active = 1";
        $params = [':tenant_id' => $tenantId];
        if ($search) {
            $sql .= " AND (title LIKE :search OR artist LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($genre) { $sql .= " AND genre = :genre"; $params[':genre'] = $genre; }
        if ($language) { $sql .= " AND language = :language"; $params[':language'] = $language; }
        $sql .= " ORDER BY play_count DESC, title LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addSong($data)
    {
        $sql = "INSERT INTO karaoke_song_catalog (tenant_id, song_code, title, artist, genre, language, year, duration_seconds, file_path, lyrics_available, date_added)
                VALUES (:tenant_id, :song_code, :title, :artist, :genre, :language, :year, :duration, :file_path, :lyrics, CURDATE())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':song_code' => $data['song_code'] ?? null,
            ':title' => $data['title'],
            ':artist' => $data['artist'] ?? null,
            ':genre' => $data['genre'] ?? null,
            ':language' => $data['language'] ?? null,
            ':year' => $data['year'] ?? null,
            ':duration' => $data['duration_seconds'] ?? null,
            ':file_path' => $data['file_path'] ?? null,
            ':lyrics' => $data['lyrics_available'] ?? 0,
        ]);
        return ['song_id' => $this->pdo->lastInsertId()];
    }

    public function getPopularSongs($tenantId, $limit = 20)
    {
        $sql = "SELECT * FROM karaoke_song_catalog WHERE tenant_id = :tenant_id AND is_active = 1 AND play_count > 0 ORDER BY play_count DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== SONG REQUESTS ====================

    public function requestSong($data)
    {
        $sql = "SELECT COALESCE(MAX(queue_position), 0) + 1 as next_pos FROM karaoke_song_requests WHERE room_id = :room_id AND status IN ('QUEUED','PLAYING')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $data['room_id']]);
        $nextPos = $stmt->fetch(PDO::FETCH_ASSOC)['next_pos'];

        $sql = "INSERT INTO karaoke_song_requests (tenant_id, branch_id, room_id, reservation_id, song_id, requested_by, request_source, queue_position)
                VALUES (:tenant_id, :branch_id, :room_id, :reservation_id, :song_id, :requested_by, :source, :pos)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':room_id' => $data['room_id'],
            ':reservation_id' => $data['reservation_id'] ?? null,
            ':song_id' => $data['song_id'],
            ':requested_by' => $data['requested_by'] ?? null,
            ':source' => $data['request_source'] ?? 'QR_APP',
            ':pos' => $nextPos,
        ]);
        return ['request_id' => $this->pdo->lastInsertId(), 'queue_position' => $nextPos];
    }

    public function getRoomQueue($roomId)
    {
        $sql = "SELECT sr.*, sc.title, sc.artist, sc.duration_seconds FROM karaoke_song_requests sr
                JOIN karaoke_song_catalog sc ON sr.song_id = sc.song_id
                WHERE sr.room_id = :room_id AND sr.status IN ('QUEUED','PLAYING')
                ORDER BY sr.queue_position";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function playNextSong($roomId)
    {
        $sql = "UPDATE karaoke_song_requests SET status = 'PLAYING', played_at = NOW() WHERE room_id = :room_id AND status = 'QUEUED' ORDER BY queue_position LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);

        $sql = "UPDATE karaoke_song_requests SET status = 'PLAYED' WHERE room_id = :room_id AND status = 'PLAYING' AND played_at < NOW() - INTERVAL 1 SECOND";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);

        $sql = "UPDATE karaoke_song_catalog sc SET play_count = play_count + 1, last_played_at = NOW()
                WHERE song_id = (SELECT song_id FROM karaoke_song_requests WHERE room_id = :room_id AND status = 'PLAYING' ORDER BY played_at DESC LIMIT 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);

        return ['success' => true];
    }

    public function skipSong($requestId)
    {
        $sql = "UPDATE karaoke_song_requests SET status = 'SKIPPED' WHERE request_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $requestId]);
        return ['success' => true];
    }

    // ==================== IN-ROOM F&B ORDERING ====================

    public function createRoomOrder($data)
    {
        $sql = "INSERT INTO karaoke_room_orders (tenant_id, branch_id, room_id, reservation_id, order_type, items_json, total_amount, ordered_by)
                VALUES (:tenant_id, :branch_id, :room_id, :reservation_id, :order_type, :items, :total, :ordered_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'],
            ':room_id' => $data['room_id'],
            ':reservation_id' => $data['reservation_id'] ?? null,
            ':order_type' => $data['order_type'] ?? 'FNB',
            ':items' => json_encode($data['items'] ?? []),
            ':total' => $data['total_amount'] ?? 0,
            ':ordered_by' => $data['ordered_by'] ?? null,
        ]);
        return ['room_order_id' => $this->pdo->lastInsertId()];
    }

    public function getRoomOrders($roomId, $status = null)
    {
        $sql = "SELECT * FROM karaoke_room_orders WHERE room_id = :room_id";
        $params = [':room_id' => $roomId];
        if ($status) { $sql .= " AND status = :status"; $params[':status'] = $status; }
        $sql .= " ORDER BY ordered_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRoomOrderStatus($roomOrderId, $status)
    {
        $servedAt = $status === 'SERVED' ? ', served_at = NOW()' : '';
        $sql = "UPDATE karaoke_room_orders SET status = :status$servedAt WHERE room_order_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $roomOrderId, ':status' => $status]);
        return ['success' => true];
    }
}
