<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Paiement;
use App\Models\Registre;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FinancialTrackingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test student can retrieve tuition payment breakdown and balance calculation.
     */
    public function test_student_payment_summary_and_remaining_balance(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Find or create a registre for this student
        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        // Clear existing test paiements for this registre
        Paiement::where('Reg_id', $registre->id)->delete();

        // Create 2 test tranches: 5000 MAD paid, 5000 MAD pending
        Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 5000.00,
            'statut' => 'Payé',
            'dateEcheance' => now()->subMonth()->toDateString(),
        ]);

        Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 5000.00,
            'statut' => 'En attente',
            'dateEcheance' => now()->addMonth()->toDateString(),
        ]);

        // Fix tuition fee to 15000.00 MAD
        $student->frais_scolarite = 15000.00;
        $student->save();

        $token = $student->createToken('test_finance_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/paiement');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'target_amount',
                    'frais_scolarite',
                    'total_paid',
                    'total_remaining',
                    'progression',
                    'tranches',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(15000.00, (float) $data['target_amount']);
        $this->assertEquals(5000.00, (float) $data['total_paid']);
        $this->assertEquals(10000.00, (float) $data['total_remaining']);
        $this->assertEquals(33.3, (float) $data['progression']);
        $this->assertCount(2, $data['tranches']);
    }

    /**
     * Test sensitive payment modifications trigger audit logging.
     */
    public function test_payment_status_modification_records_audit_log(): void
    {
        $paiement = Paiement::first();
        $this->assertNotNull($paiement, 'A payment record should exist in database.');

        $initialAuditCount = AuditLog::where('subject_type', 'Paiement')->count();

        // Update payment status
        $originalStatus = $paiement->statut;
        $newStatus = ($originalStatus === 'Payé') ? 'En attente' : 'Payé';
        $paiement->statut = $newStatus;
        $paiement->save();

        $newAuditCount = AuditLog::where('subject_type', 'Paiement')->count();
        $this->assertGreaterThan(
            $initialAuditCount,
            $newAuditCount,
            'PaiementObserver must record an audit log entry when payment status is modified.'
        );

        $latestLog = AuditLog::where('subject_type', 'Paiement')->latest()->first();
        $this->assertEquals('Modification Paiement', $latestLog->action);
        $this->assertArrayHasKey('old_values', $latestLog->details);
        $this->assertArrayHasKey('new_values', $latestLog->details);
    }

    /**
     * Test admin revenue aggregation endpoint.
     */
    public function test_admin_can_access_revenue_summary(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $token = $admin->createToken('admin_test_token', ['role:admin'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/revenue');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_revenue',
                    'pending_revenue',
                    'overdue_payments',
                    'currency',
                ],
            ]);
    }
}
