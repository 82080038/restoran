<?php

namespace App\Modules\KaraokeAdvanced\Controllers;

use App\Core\Response;
use App\Modules\KaraokeAdvanced\Services\KaraokeAdvancedService;

class KaraokeAdvancedController extends BaseController
{
    private $service;

    public function __construct()
    {
        $this->service = new KaraokeAdvancedService();
    }

    public function getSongs($request)
    {
        try {
            $result = $this->service->getSongs(
                $request['tenant_id'], $request['query']['search'] ?? null,
                $request['query']['genre'] ?? null, $request['query']['language'] ?? null,
                (int)($request['query']['limit'] ?? 100), (int)($request['query']['offset'] ?? 0)
            );
            return Response::success($result, 'Songs retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getPopularSongs($request)
    {
        try {
            $result = $this->service->getPopularSongs($request['tenant_id'], (int)($request['query']['limit'] ?? 20));
            return Response::success($result, 'Popular songs retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function addSong($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            if (empty($data['title'])) {
                return Response::error('title is required', 400);
            }
            return Response::success($this->service->addSong($data), 'Song added');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function requestSong($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['room_id']) || empty($data['song_id'])) {
                return Response::error('room_id and song_id are required', 400);
            }
            return Response::success($this->service->requestSong($data), 'Song requested');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getRoomQueue($request)
    {
        try {
            $roomId = $request['params']['room_id'] ?? $request['query']['room_id'] ?? null;
            return Response::success($this->service->getRoomQueue($roomId), 'Room queue retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function playNextSong($request)
    {
        try {
            $roomId = $request['params']['room_id'] ?? $request['body']['room_id'] ?? null;
            return Response::success($this->service->playNextSong($roomId), 'Next song playing');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function skipSong($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            return Response::success($this->service->skipSong($id), 'Song skipped');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function createRoomOrder($request)
    {
        try {
            $data = $request['body'];
            $data['tenant_id'] = $request['tenant_id'];
            $data['branch_id'] = $request['branch_id'] ?? $data['branch_id'] ?? null;
            if (empty($data['room_id'])) {
                return Response::error('room_id is required', 400);
            }
            return Response::success($this->service->createRoomOrder($data), 'Room order created');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function getRoomOrders($request)
    {
        try {
            $roomId = $request['params']['room_id'] ?? $request['query']['room_id'] ?? null;
            return Response::success($this->service->getRoomOrders($roomId, $request['query']['status'] ?? null), 'Room orders retrieved');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }

    public function updateRoomOrderStatus($request)
    {
        try {
            $id = $request['params']['id'] ?? $request['id'] ?? null;
            $status = $request['body']['status'] ?? null;
            if (!$status) return Response::error('status is required', 400);
            return Response::success($this->service->updateRoomOrderStatus($id, $status), 'Room order status updated');
        } catch (\Exception $e) { return Response::error($e->getMessage(), 500); }
    }
}
