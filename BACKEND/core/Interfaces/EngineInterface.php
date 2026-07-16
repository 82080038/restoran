<?php

namespace App\Core;

/**
 * Engine Interface
 * 
 * Base interface for all business logic engines
 * Ensures consistent structure and behavior across all engines
 * 
 * @package EBP\App\Core\Interfaces
 * @version 1.0.0
 * @date 2026-07-08
 */

interface EngineInterface
{
    /**
     * Initialize the engine with required dependencies
     * 
     * @param mixed $dependencies Database connection, services, etc.
     * @return void
     */
    public function initialize($dependencies): void;

    /**
     * Validate that the engine has all required dependencies
     * 
     * @return bool True if valid, false otherwise
     */
    public function validate(): bool;

    /**
     * Execute the engine's primary operation
     * 
     * @param array $params Parameters for the operation
     * @return array Result of the operation
     */
    public function execute(array $params): array;

    /**
     * Get engine metadata
     * 
     * @return array Engine information (name, version, description, etc.)
     */
    public function getMetadata(): array;

    /**
     * Get engine health status
     * 
     * @return array Health information
     */
    public function getHealth(): array;
}
