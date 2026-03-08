<?php
/**
 * Unit Tests: Installation Tracker Validierung.
 *
 * @package SearchForge_License_Manager\Tests
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class InstallationTrackerTest extends TestCase {

    /**
     * Test: Fingerprint-Validierung (64 Hex-Zeichen).
     */
    public function test_validate_fingerprint_valid(): void {
        // 64 lowercase hex characters (SHA-256 Hash)
        $this->assertTrue(
            SFLM_Installation_Tracker::validate_fingerprint( 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2' )
        );
        $this->assertTrue(
            SFLM_Installation_Tracker::validate_fingerprint( '0000000000000000000000000000000000000000000000000000000000000000' )
        );
        $this->assertTrue(
            SFLM_Installation_Tracker::validate_fingerprint( 'ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff' )
        );
    }

    /**
     * Test: Ungültige Fingerprints werden abgelehnt.
     */
    public function test_validate_fingerprint_invalid(): void {
        // Zu kurz
        $this->assertFalse( SFLM_Installation_Tracker::validate_fingerprint( 'a1b2c3' ) );
        // Zu lang
        $this->assertFalse( SFLM_Installation_Tracker::validate_fingerprint( str_repeat( 'a', 65 ) ) );
        // Uppercase
        $this->assertFalse( SFLM_Installation_Tracker::validate_fingerprint( str_repeat( 'A', 64 ) ) );
        // Non-hex
        $this->assertFalse( SFLM_Installation_Tracker::validate_fingerprint( str_repeat( 'g', 64 ) ) );
        // Leer
        $this->assertFalse( SFLM_Installation_Tracker::validate_fingerprint( '' ) );
    }

    /**
     * Test: Plattform-Validierung.
     */
    public function test_validate_platform_valid(): void {
        $this->assertTrue( SFLM_Installation_Tracker::validate_platform( 'nodejs' ) );
        $this->assertTrue( SFLM_Installation_Tracker::validate_platform( 'docker' ) );
        $this->assertTrue( SFLM_Installation_Tracker::validate_platform( 'wordpress' ) );
        $this->assertTrue( SFLM_Installation_Tracker::validate_platform( 'drupal' ) );
    }

    /**
     * Test: Unbekannte Plattformen werden abgelehnt.
     */
    public function test_validate_platform_invalid(): void {
        $this->assertFalse( SFLM_Installation_Tracker::validate_platform( 'python' ) );
        $this->assertFalse( SFLM_Installation_Tracker::validate_platform( 'NodeJS' ) ); // case-sensitive
        $this->assertFalse( SFLM_Installation_Tracker::validate_platform( '' ) );
        $this->assertFalse( SFLM_Installation_Tracker::validate_platform( 'node.js' ) );
    }
}
