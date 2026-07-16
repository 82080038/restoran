<?php

if (!class_exists('ReservationRepository')) {
    require_once __DIR__ . '/../Repositories/ReservationRepository.php';
}



class ReservationService
{
    private $reservationRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->reservationRepository = new ReservationRepository();
        $this->transaction = new Transaction();
        $this->audit = new \App\Core\Audit();
    }

    public function getAllReservations(int $tenantId, ?int $branchId = null): array
    {
        $reservations = $this->reservationRepository->findAll($tenantId, $branchId);
        return array_map(function($r) { return $r->toArray(); }, $reservations);
    }

    public function getReservationsByDate(int $tenantId, int $branchId, string $date): array
    {
        $reservations = $this->reservationRepository->findByDate($tenantId, $branchId, $date);
        return array_map(function($r) { return $r->toArray(); }, $reservations);
    }

    public function getReservation(int $tenantId, int $reservationId): ?array
    {
        $reservation = $this->reservationRepository->findById($tenantId, $reservationId);
        return $reservation ? $reservation->toArray() : null;
    }

    public function checkAvailability(int $tenantId, int $branchId, string $date, string $time, int $partySize): bool
    {
        return $this->reservationRepository->checkAvailability($tenantId, $branchId, $date, $time, $partySize);
    }

    public function createReservation(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            
            // Generate reservation number
            $data['reservation_number'] = $this->reservationRepository->generateReservationNumber(
                $tenantId,
                $data['branch_id']
            );
            
            $reservation = new \App\Modules\Reservation\Models\Reservation($data);
            
            // Check availability before creating
            $isAvailable = $this->reservationRepository->checkAvailability(
                $tenantId,
                $reservation->branch_id,
                $reservation->reservation_date,
                $reservation->reservation_time,
                $reservation->party_size
            );
            
            if (!$isAvailable) {
                $this->transaction->rollback();
                return false;
            }
            
            $result = $this->reservationRepository->create($reservation);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'RESERVATION',
                    'CREATE_RESERVATION',
                    $reservation->reservation_id,
                    'reservations',
                    null,
                    ['reservation_number' => $reservation->reservation_number, 'date' => $reservation->reservation_date, 'party_size' => $reservation->party_size]
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateReservation(int $tenantId, int $reservationId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $data['tenant_id'] = $tenantId;
            $data['reservation_id'] = $reservationId;
            $reservation = new \App\Modules\Reservation\Models\Reservation($data);
            
            // If date or time changed, check availability
            if (isset($data['reservation_date']) || isset($data['reservation_time'])) {
                $checkDate = $data['reservation_date'] ?? $oldReservation->reservation_date;
                $checkTime = $data['reservation_time'] ?? $oldReservation->reservation_time;
                
                $isAvailable = $this->reservationRepository->checkAvailability(
                    $tenantId,
                    $reservation->branch_id,
                    $checkDate,
                    $checkTime,
                    $reservation->party_size
                );
                
                if (!$isAvailable) {
                    $this->transaction->rollback();
                    return false;
                }
            }
            
            $result = $this->reservationRepository->update($reservation);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'RESERVATION',
                    'UPDATE_RESERVATION',
                    $reservationId,
                    'reservations',
                    $oldReservation ? $oldReservation->toArray() : null,
                    $reservation->toArray()
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateReservationStatus(int $tenantId, int $reservationId, string $status): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $result = $this->reservationRepository->updateStatus($tenantId, $reservationId, $status);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'RESERVATION',
                    'UPDATE_RESERVATION_STATUS',
                    $reservationId,
                    'reservations',
                    ['old_status' => $oldReservation ? $oldReservation->status : null],
                    ['new_status' => $status]
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteReservation(int $tenantId, int $reservationId): bool
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            
            $result = $this->reservationRepository->delete($tenantId, $reservationId);
            
            if ($result) {
                $this->audit->log(
                    $tenantId,
                    $_SESSION['user_id'] ?? null,
                    'RESERVATION',
                    'DELETE_RESERVATION',
                    $reservationId,
                    'reservations',
                    $oldReservation ? $oldReservation->toArray() : null,
                    null
                );
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * Real-time availability check with conflict detection
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @param int $partySize Number of guests
     * @param int $excludeReservationId Reservation ID to exclude from check (for updates)
     * @return array Availability result with conflicts
     */
    public function checkRealTimeAvailability(int $tenantId, int $branchId, string $date, string $time, int $partySize, int $excludeReservationId = null): array
    {
        // Get existing reservations for the date
        $existingReservations = $this->reservationRepository->findByDate($tenantId, $branchId, $date);
        
        // Filter for time conflicts (within 2 hours window)
        $conflicts = [];
        $availableTables = [];
        
        foreach ($existingReservations as $reservation) {
            if ($excludeReservationId && $reservation->reservation_id == $excludeReservationId) {
                continue;
            }
            
            $reservationTime = strtotime($reservation->reservation_time);
            $requestedTime = strtotime($time);
            $timeDiff = abs($reservationTime - $requestedTime);
            
            // Check if reservations overlap (within 2 hours)
            if ($timeDiff <= 7200) { // 2 hours in seconds
                $conflicts[] = [
                    'reservation_id' => $reservation->reservation_id,
                    'reservation_number' => $reservation->reservation_number,
                    'time' => $reservation->reservation_time,
                    'party_size' => $reservation->party_size,
                    'table_id' => $reservation->table_id
                ];
            }
        }
        
        // Get available tables
        $availableTables = $this->getAvailableTablesForPartySize($tenantId, $branchId, $partySize, $date, $time);
        
        return [
            'available' => empty($conflicts) && !empty($availableTables),
            'conflicts' => $conflicts,
            'available_tables' => $availableTables,
            'party_size' => $partySize,
            'requested_time' => $time,
            'total_conflicts' => count($conflicts)
        ];
    }

    /**
     * Get available tables for a specific party size and time
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $partySize Number of guests
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @return array Available tables
     */
    private function getAvailableTablesForPartySize(int $tenantId, int $branchId, int $partySize, string $date, string $time): array
    {
        // Get all tables at the branch
        global $db;
        
        $sql = "
            SELECT t.table_id, t.table_number, t.capacity, t.status
            FROM restaurant_tables t
            WHERE t.tenant_id = ? 
              AND t.branch_id = ?
              AND t.status = 'ACTIVE'
            ORDER BY t.capacity ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filter tables that can accommodate the party size
        $suitableTables = array_filter($tables, function($table) use ($partySize) {
            return $table['capacity'] >= $partySize;
        });
        
        // Get tables that are already reserved for this time slot
        $reservedTableIds = $this->getReservedTableIds($tenantId, $branchId, $date, $time);
        
        // Filter out reserved tables
        $availableTables = array_filter($suitableTables, function($table) use ($reservedTableIds) {
            return !in_array($table['table_id'], $reservedTableIds);
        });
        
        return array_values($availableTables);
    }

    /**
     * Get IDs of tables already reserved for a specific time
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @return array Reserved table IDs
     */
    private function getReservedTableIds(int $tenantId, int $branchId, string $date, string $time): array
    {
        global $db;
        
        $sql = "
            SELECT DISTINCT table_id
            FROM reservations
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND reservation_date = ?
              AND status NOT IN ('CANCELLED', 'NO_SHOW', 'COMPLETED')
              AND ABS(TIME_TO_SEC(TIMEDIFF(reservation_time, ?))) <= 7200
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $date, $time]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_column($results, 'table_id');
    }

    /**
     * Auto-assign optimal table for reservation
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @param int $partySize Number of guests
     * @return int|null Optimal table ID or null if none available
     */
    public function autoAssignTable(int $tenantId, int $branchId, string $date, string $time, int $partySize): ?int
    {
        $availableTables = $this->getAvailableTablesForPartySize($tenantId, $branchId, $partySize, $date, $time);
        
        if (empty($availableTables)) {
            return null;
        }
        
        // Find table with capacity closest to party size (optimal fit)
        usort($availableTables, function($a, $b) use ($partySize) {
            $diffA = $a['capacity'] - $partySize;
            $diffB = $b['capacity'] - $partySize;
            return $diffA - $diffB;
        });
        
        return $availableTables[0]['table_id'];
    }

    /**
     * Create reservation with real-time availability check
     * 
     * @param int $tenantId Tenant ID
     * @param array $data Reservation data
     * @return array Creation result with table assignment
     */
    public function createReservationWithRealTimeCheck(int $tenantId, array $data): array
    {
        $this->transaction->begin();
        
        try {
            $data['tenant_id'] = $tenantId;
            
            // Real-time availability check
            $availability = $this->checkRealTimeAvailability(
                $tenantId,
                $data['branch_id'],
                $data['reservation_date'],
                $data['reservation_time'],
                $data['party_size']
            );
            
            if (!$availability['available']) {
                $this->transaction->rollback();
                return [
                    'success' => false,
                    'message' => 'No available tables for the requested time',
                    'availability' => $availability
                ];
            }
            
            // Auto-assign table if not specified
            if (!isset($data['table_id']) || empty($data['table_id'])) {
                $tableId = $this->autoAssignTable(
                    $tenantId,
                    $data['branch_id'],
                    $data['reservation_date'],
                    $data['reservation_time'],
                    $data['party_size']
                );
                
                if (!$tableId) {
                    $this->transaction->rollback();
                    return [
                        'success' => false,
                        'message' => 'No suitable table available',
                        'availability' => $availability
                    ];
                }
                
                $data['table_id'] = $tableId;
            }
            
            // Generate reservation number
            $data['reservation_number'] = $this->reservationRepository->generateReservationNumber(
                $tenantId,
                $data['branch_id']
            );
            
            $reservation = new \App\Modules\Reservation\Models\Reservation($data);
            $result = $this->reservationRepository->create($reservation);
            
            if ($result) {
                // Trigger real-time notification
                $this->triggerReservationNotification($tenantId, $data['branch_id'], $reservation);
                
                $this->transaction->commit();
                
                return [
                    'success' => true,
                    'reservation' => $reservation->toArray(),
                    'table_assigned' => $data['table_id'],
                    'availability' => $availability
                ];
            }
            
            $this->transaction->rollback();
            return [
                'success' => false,
                'message' => 'Failed to create reservation'
            ];
            
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * Trigger real-time notification for new reservation
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param Reservation $reservation Reservation object
     */
    private function triggerReservationNotification(int $tenantId, int $branchId, $reservation): void
    {
        // Log notification event
        global $db;
        
        $sql = "
            INSERT INTO reservation_notifications
            (tenant_id, branch_id, reservation_id, notification_type, message, created_at, status)
            VALUES (?, ?, ?, 'NEW_RESERVATION', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $reservation->reservation_id,
            "New reservation #{$reservation->reservation_number} for {$reservation->reservation_date} at {$reservation->reservation_time}"
        ]);
    }

    /**
     * Get real-time reservation dashboard data
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @return array Dashboard data
     */
    public function getRealTimeDashboard(int $tenantId, int $branchId, string $date): array
    {
        $reservations = $this->getReservationsByDate($tenantId, $branchId, $date);
        
        // Calculate real-time metrics
        $metrics = [
            'total_reservations' => count($reservations),
            'confirmed' => count(array_filter($reservations, fn($r) => $r['status'] === 'CONFIRMED')),
            'pending' => count(array_filter($reservations, fn($r) => $r['status'] === 'PENDING')),
            'seated' => count(array_filter($reservations, fn($r) => $r['status'] === 'SEATED')),
            'completed' => count(array_filter($reservations, fn($r) => $r['status'] === 'COMPLETED')),
            'cancelled' => count(array_filter($reservations, fn($r) => $r['status'] === 'CANCELLED')),
            'total_guests' => array_sum(array_column($reservations, 'party_size')),
            'table_utilization' => $this->calculateTableUtilization($tenantId, $branchId, $date)
        ];
        
        // Get time slot availability
        $timeSlots = $this->getTimeSlotAvailability($tenantId, $branchId, $date);
        
        return [
            'date' => $date,
            'metrics' => $metrics,
            'reservations' => $reservations,
            'time_slots' => $timeSlots,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calculate table utilization for a specific date
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @return array Table utilization data
     */
    private function calculateTableUtilization(int $tenantId, int $branchId, string $date): array
    {
        global $db;
        
        // Get total tables
        $sql = "
            SELECT COUNT(*) as total_tables
            FROM restaurant_tables
            WHERE tenant_id = ? AND branch_id = ? AND status = 'ACTIVE'
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $totalTables = $stmt->fetch(PDO::FETCH_ASSOC)['total_tables'];
        
        // Get utilized tables for the date
        $sql = "
            SELECT COUNT(DISTINCT table_id) as utilized_tables
            FROM reservations
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND reservation_date = ?
              AND status NOT IN ('CANCELLED', 'NO_SHOW')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $date]);
        $utilizedTables = $stmt->fetch(PDO::FETCH_ASSOC)['utilized_tables'];
        
        $utilizationRate = $totalTables > 0 ? ($utilizedTables / $totalTables) * 100 : 0;
        
        return [
            'total_tables' => $totalTables,
            'utilized_tables' => $utilizedTables,
            'utilization_rate' => round($utilizationRate, 2),
            'available_tables' => $totalTables - $utilizedTables
        ];
    }

    /**
     * Get time slot availability for a specific date
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @return array Time slot availability data
     */
    private function getTimeSlotAvailability(int $tenantId, int $branchId, string $date): array
    {
        $timeSlots = [];
        $startTime = strtotime('11:00'); // Opening time
        $endTime = strtotime('22:00'); // Closing time
        $interval = 1800; // 30-minute intervals
        
        for ($time = $startTime; $time < $endTime; $time += $interval) {
            $timeStr = date('H:i', $time);
            $availability = $this->checkRealTimeAvailability($tenantId, $branchId, $date, $timeStr, 2);
            
            $timeSlots[] = [
                'time' => $timeStr,
                'available' => $availability['available'],
                'available_tables' => count($availability['available_tables']),
                'conflicts' => $availability['total_conflicts']
            ];
        }
        
        return $timeSlots;
    }

    /**
     * Handle reservation status change with real-time updates
     * 
     * @param int $tenantId Tenant ID
     * @param int $reservationId Reservation ID
     * @param string $newStatus New status
     * @param string $reason Reason for status change
     * @return array Status change result
     */
    public function handleStatusChangeWithRealTimeUpdate(int $tenantId, int $reservationId, string $newStatus, string $reason = ''): array
    {
        $this->transaction->begin();
        
        try {
            $oldReservation = $this->reservationRepository->findById($tenantId, $reservationId);
            $oldStatus = $oldReservation->status;
            
            $result = $this->reservationRepository->updateStatus($tenantId, $reservationId, $newStatus);
            
            if ($result) {
                // Log status change
                $this->logStatusChange($tenantId, $reservationId, $oldStatus, $newStatus, $reason);
                
                // Trigger notification based on status change
                $this->triggerStatusChangeNotification($tenantId, $oldReservation->branch_id, $reservationId, $oldStatus, $newStatus);
                
                // If reservation is cancelled, release the table for real-time availability
                if ($newStatus === 'CANCELLED' || $newStatus === 'NO_SHOW') {
                    $this->releaseTableForRealTime($tenantId, $oldReservation->branch_id, $oldReservation->table_id, $oldReservation->reservation_date, $oldReservation->reservation_time);
                }
                
                $this->transaction->commit();
                
                return [
                    'success' => true,
                    'reservation_id' => $reservationId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            
            $this->transaction->rollback();
            return [
                'success' => false,
                'message' => 'Failed to update reservation status'
            ];
            
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * Log status change
     */
    private function logStatusChange(int $tenantId, int $reservationId, string $oldStatus, string $newStatus, string $reason): void
    {
        global $db;
        
        $sql = "
            INSERT INTO reservation_status_history
            (tenant_id, reservation_id, old_status, new_status, reason, changed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $reservationId, $oldStatus, $newStatus, $reason]);
    }

    /**
     * Trigger status change notification
     */
    private function triggerStatusChangeNotification(int $tenantId, int $branchId, int $reservationId, string $oldStatus, string $newStatus): void
    {
        global $db;
        
        $sql = "
            INSERT INTO reservation_notifications
            (tenant_id, branch_id, reservation_id, notification_type, message, created_at, status)
            VALUES (?, ?, ?, 'STATUS_CHANGE', ?, NOW(), 'PENDING')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $reservationId,
            "Reservation #{$reservationId} status changed from {$oldStatus} to {$newStatus}"
        ]);
    }

    /**
     * Release table for real-time availability
     */
    private function releaseTableForRealTime(int $tenantId, int $branchId, int $tableId, string $date, string $time): void
    {
        // Update real-time availability cache
        // This would typically update a Redis cache or similar
        // For now, we'll log the action
        global $db;
        
        $sql = "
            INSERT INTO table_availability_log
            (tenant_id, branch_id, table_id, reservation_date, reservation_time, action, logged_at)
            VALUES (?, ?, ?, ?, ?, 'RELEASED', NOW())
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $tableId, $date, $time]);
    }

    /**
     * Get real-time waiting list status
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param string $date Date in Y-m-d format
     * @return array Waiting list status
     */
    public function getWaitingListStatus(int $tenantId, int $branchId, string $date): array
    {
        global $db;
        
        $sql = "
            SELECT 
                COUNT(*) as total_waiting,
                SUM(CASE WHEN status = 'WAITING' THEN 1 ELSE 0 END) as actively_waiting,
                AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as avg_wait_minutes
            FROM reservation_waiting_list
            WHERE tenant_id = ? 
              AND branch_id = ?
              AND reservation_date = ?
              AND status = 'WAITING'
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $date]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get individual waiting list entries
        $sql = "
            SELECT w.*, r.reservation_number
            FROM reservation_waiting_list w
            LEFT JOIN reservations r ON w.reservation_id = r.reservation_id
            WHERE w.tenant_id = ? 
              AND w.branch_id = ?
              AND w.reservation_date = ?
              AND w.status = 'WAITING'
            ORDER BY w.created_at ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $date]);
        $waitingList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'date' => $date,
            'statistics' => $stats,
            'waiting_list' => $waitingList,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Add to waiting list
     * 
     * @param int $tenantId Tenant ID
     * @param array $data Waiting list data
     * @return array Result
     */
    public function addToWaitingList(int $tenantId, array $data): array
    {
        global $db;
        
        $sql = "
            INSERT INTO reservation_waiting_list
            (tenant_id, branch_id, customer_id, reservation_date, party_size, contact_info, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'WAITING', NOW())
        ";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $tenantId,
            $data['branch_id'],
            $data['customer_id'] ?? null,
            $data['reservation_date'],
            $data['party_size'],
            json_encode($data['contact_info'] ?? [])
        ]);
        
        return [
            'success' => $result,
            'waiting_list_id' => $result ? $db->lastInsertId() : null,
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
}
