<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Planning;

class TimetableConflictTest extends TestCase
{
    /**
     * Helper to detect timetable conflicts between two sessions.
     * Returns true if there is an overlapping time conflict for the same resource (professor or room).
     */
    protected function hasScheduleConflict(array $slotA, array $slotB): bool
    {
        // Must be on the same day or same specific date
        $sameDay = false;
        if (!empty($slotA['date']) && !empty($slotB['date'])) {
            $sameDay = ($slotA['date'] === $slotB['date']);
        } elseif (!empty($slotA['jour']) && !empty($slotB['jour'])) {
            $sameDay = (mb_strtolower($slotA['jour']) === mb_strtolower($slotB['jour']));
        }

        if (!$sameDay) {
            return false;
        }

        // Check if same professor or same room
        $sameProfessor = (!empty($slotA['professeur_name']) && !empty($slotB['professeur_name']))
            && (mb_strtolower(trim($slotA['professeur_name'])) === mb_strtolower(trim($slotB['professeur_name'])));

        $sameRoom = (!empty($slotA['salle']) && !empty($slotB['salle']))
            && (mb_strtolower(trim($slotA['salle'])) === mb_strtolower(trim($slotB['salle'])));

        if (!$sameProfessor && !$sameRoom) {
            return false;
        }

        // Time overlap check: startA < endB and endA > startB
        $startA = strtotime($slotA['check_in']);
        $endA   = strtotime($slotA['check_out']);
        $startB = strtotime($slotB['check_in']);
        $endB   = strtotime($slotB['check_out']);

        return ($startA < $endB && $endA > $startB);
    }

    /**
     * Test that overlapping sessions on the same day for the same professor trigger a conflict.
     */
    public function test_professor_cannot_be_scheduled_in_two_places_simultaneously(): void
    {
        $session1 = [
            'jour' => 'Lundi',
            'professeur_name' => 'Prof. Zakaria Mansouri',
            'check_in' => '08:30:00',
            'check_out' => '10:30:00',
            'salle' => 'Salle 101',
        ];

        // Overlapping slot: 09:30 - 11:30
        $session2 = [
            'jour' => 'Lundi',
            'professeur_name' => 'Prof. Zakaria Mansouri',
            'check_in' => '09:30:00',
            'check_out' => '11:30:00',
            'salle' => 'Salle 202',
        ];

        $this->assertTrue(
            $this->hasScheduleConflict($session1, $session2),
            'An overlapping session for the same professor must be flagged as a timetable conflict.'
        );
    }

    /**
     * Test adjacent sessions (one ends exactly when next starts) do NOT conflict.
     */
    public function test_adjacent_sessions_do_not_conflict(): void
    {
        $session1 = [
            'jour' => 'Mardi',
            'professeur_name' => 'Prof. Zakaria Mansouri',
            'check_in' => '08:30:00',
            'check_out' => '10:30:00',
            'salle' => 'Salle 101',
        ];

        // Starts exactly when session 1 ends
        $session2 = [
            'jour' => 'Mardi',
            'professeur_name' => 'Prof. Zakaria Mansouri',
            'check_in' => '10:30:00',
            'check_out' => '12:30:00',
            'salle' => 'Salle 102',
        ];

        $this->assertFalse(
            $this->hasScheduleConflict($session1, $session2),
            'Adjacent sessions with no overlap should not conflict.'
        );
    }

    /**
     * Test same hours on different days do NOT conflict.
     */
    public function test_sessions_on_different_days_do_not_conflict(): void
    {
        $sessionMonday = [
            'jour' => 'Lundi',
            'professeur_name' => 'Prof. Yassmine Bennani',
            'check_in' => '08:30:00',
            'check_out' => '10:30:00',
            'salle' => 'Labo 1',
        ];

        $sessionTuesday = [
            'jour' => 'Mardi',
            'professeur_name' => 'Prof. Yassmine Bennani',
            'check_in' => '08:30:00',
            'check_out' => '10:30:00',
            'salle' => 'Labo 1',
        ];

        $this->assertFalse(
            $this->hasScheduleConflict($sessionMonday, $sessionTuesday),
            'Sessions on different days must not conflict.'
        );
    }

    /**
     * Test different professors in different rooms at the same time do NOT conflict.
     */
    public function test_different_professors_at_same_time_do_not_conflict(): void
    {
        $prof1 = [
            'jour' => 'Mercredi',
            'professeur_name' => 'Prof. Amine El Idrissi',
            'check_in' => '14:00:00',
            'check_out' => '16:00:00',
            'salle' => 'Salle A',
        ];

        $prof2 = [
            'jour' => 'Mercredi',
            'professeur_name' => 'Prof. Rania Tazi',
            'check_in' => '14:00:00',
            'check_out' => '16:00:00',
            'salle' => 'Salle B',
        ];

        $this->assertFalse(
            $this->hasScheduleConflict($prof1, $prof2),
            'Different professors in different rooms at the same time should not conflict.'
        );
    }

    /**
     * Test room double booking conflict detection.
     */
    public function test_room_double_booking_triggers_conflict(): void
    {
        $classA = [
            'jour' => 'Jeudi',
            'professeur_name' => 'Prof. Bilal Chraibi',
            'check_in' => '10:00:00',
            'check_out' => '12:00:00',
            'salle' => 'Amphi 1',
        ];

        $classB = [
            'jour' => 'Jeudi',
            'professeur_name' => 'Prof. Salma Berrada',
            'check_in' => '11:00:00',
            'check_out' => '13:00:00',
            'salle' => 'Amphi 1', // Same room, overlapping time
        ];

        $this->assertTrue(
            $this->hasScheduleConflict($classA, $classB),
            'Two classes scheduled in the same room at the same time must trigger a conflict.'
        );
    }
}
