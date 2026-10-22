<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use App\Models\Student;
use App\Models\Paiement;
use App\Models\Registre;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdvancedFinancialManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
    }

    /**
     * Test payment creation generates unique reference (REF-YYYY-XXXXX) inside a DB transaction.
     */
    public function test_payment_creation_with_unique_reference_and_db_transaction(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $payment = $this->paymentService->recordPayment([
            'Reg_id' => $registre->id,
            'montant' => 4500.00,
            'statut' => 'Payé',
            'modePaiement' => 'Chèque',
            'dateEcheance' => now()->toDateString(),
            'notes' => 'Chèque BMCE #987654',
        ], $admin);

        $this->assertNotNull($payment->id);
        $this->assertNotNull($payment->reference);
        $this->assertStringStartsWith('REF-' . date('Y'), $payment->reference);
        $this->assertEquals(4500.00, (float) $payment->montant);
        $this->assertEquals('Chèque', $payment->modePaiement);
        $this->assertEquals($admin->id, $payment->recorded_by);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'PAIEMENT_CREATE',
            'subject_id' => (string) $payment->id,
        ]);
    }

    /**
     * Test scholarship & discount automatically adjust student target_amount and remaining balance.
     */
    public function test_scholarship_and_discount_adjusts_target_amount_and_remaining_balance(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        Paiement::where('Reg_id', $registre->id)->delete();

        // 1. Setup base tuition = 15000 MAD
        $student->frais_scolarite = 15000.00;
        $student->save();

        // 2. Grant a merit scholarship of 3000 MAD
        $admin = User::where('role', 'admin')->first();
        $this->paymentService->updateStudentDiscount(
            $student,
            3000.00,
            'bourse_merite',
            'Excellence académique mention Très Bien',
            $admin
        );

        $student->refresh();
        $this->assertEquals(15000.00, $student->base_tuition);
        $this->assertEquals(3000.00, $student->remise_montant_total);
        $this->assertEquals('bourse_merite', $student->remise_type);

        // Target amount must now be 15000 - 3000 = 12000 MAD
        $this->assertEquals(12000.00, $student->frais_scolarite_total);
        $this->assertEquals(12000.00, $student->target_amount);

        // 3. Make a payment of 5000 MAD
        Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 5000.00,
            'statut' => 'Payé',
            'dateEcheance' => now()->toDateString(),
        ]);

        $student->refresh();
        $this->assertEquals(5000.00, $student->total_paye);

        // Remaining balance must be 12000 - 5000 = 7000 MAD
        $this->assertEquals(7000.00, $student->reste_a_payer);
        $this->assertEquals(41.7, $student->pourcentage_paye);
    }

    /**
     * Test payment update tracks audit log with old and new values inside transaction.
     */
    public function test_payment_update_tracks_audit_log_inside_transaction(): void
    {
        $student = Student::first();
        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        $admin = User::where('role', 'admin')->first();

        $payment = Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 2000.00,
            'statut' => 'Non Payé',
            'modePaiement' => 'Espèce',
            'dateEcheance' => now()->toDateString(),
        ]);

        $updatedPayment = $this->paymentService->updatePayment($payment, [
            'montant' => 2500.00,
            'statut' => 'Payé',
            'modePaiement' => 'Virement',
        ], $admin);

        $this->assertEquals(2500.00, (float) $updatedPayment->montant);
        $this->assertEquals('Payé', $updatedPayment->statut);
        $this->assertEquals('Virement', $updatedPayment->modePaiement);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'PAIEMENT_UPDATE',
            'subject_id' => (string) $payment->id,
        ]);
    }

    /**
     * Test PDF receipt generation produces a valid PDF document with header & reference.
     */
    public function test_pdf_receipt_generation_produces_valid_pdf_content(): void
    {
        $student = Student::first();
        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        $payment = Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 3500.00,
            'statut' => 'Payé',
            'modePaiement' => 'Espèce',
            'dateEcheance' => now()->toDateString(),
        ]);

        $pdf = $this->paymentService->generateReceiptPdf($payment);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        // PDF magic byte signature: %PDF-
        $this->assertStringStartsWith('%PDF-', $output);
    }

    /**
     * Test authorized staff and signed URL can download payment receipt.
     */
    public function test_authorized_staff_can_download_payment_receipt(): void
    {
        $student = Student::first();
        $registre = Registre::firstOrCreate(
            ['idStudent' => $student->idStudent],
            ['Cla_id' => 1]
        );

        $payment = Paiement::create([
            'Reg_id' => $registre->id,
            'montant' => 5000.00,
            'statut' => 'Payé',
            'modePaiement' => 'Chèque',
            'dateEcheance' => now()->toDateString(),
        ]);

        $admin = User::where('role', 'admin')->first();

        // 1. Staff via session/sanctum
        $response = $this->actingAs($admin)
            ->get("/paiements/{$payment->id}/receipt");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // 2. Via valid HMAC signed URL
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'paiements.receipt.download',
            now()->addMinutes(30),
            ['id' => $payment->id]
        );

        $signedResponse = $this->get($signedUrl);
        $signedResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $signedResponse->headers->get('content-type'));
    }

    /**
     * Test unauthorized student cannot download other student's receipt (403 Forbidden).
     */
    public function test_unauthorized_user_cannot_download_other_student_receipt(): void
    {
        $students = Student::limit(2)->get();
        if ($students->count() < 2) {
            $this->markTestSkipped('Need at least 2 students for IDOR receipt test.');
        }

        $studentA = $students[0];
        $studentB = $students[1];

        $registreB = Registre::firstOrCreate(
            ['idStudent' => $studentB->idStudent],
            ['Cla_id' => 1]
        );

        $paymentB = Paiement::create([
            'Reg_id' => $registreB->id,
            'montant' => 4000.00,
            'statut' => 'Payé',
            'dateEcheance' => now()->toDateString(),
        ]);

        // Student A attempts to download Student B's receipt
        $response = $this->actingAs($studentA)
            ->get("/paiements/{$paymentB->id}/receipt");

        $response->assertStatus(403);
    }

    /**
     * Test admin can retrieve financial summary analytics and monthly breakdown.
     */
    public function test_admin_can_retrieve_financial_summary_and_reports(): void
    {
        $admin = User::where('role', 'admin')->first();
        $token = $admin->createToken('test_admin_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/financial-summary');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'year',
                    'total_collected_all_time',
                    'year_collected',
                    'month_collected',
                    'total_students',
                    'total_base_tuition',
                    'total_discounts_granted',
                    'total_target_tuition',
                    'total_remaining_balance',
                    'collection_rate',
                    'monthly_revenue',
                    'payment_methods',
                    'recent_transactions',
                ]
            ]);
    }

    /**
     * Test FinancialAuditReports component filters and access control.
     */
    public function test_financial_audit_reports_page_access_and_filtering(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $page = new \App\Filament\Pages\FinancialAuditReports();
        $page->mount();

        $this->assertEquals((int) date('Y'), $page->selectedYear);
        $this->assertIsArray($page->availableYears);
        $this->assertNotEmpty($page->availableYears);
        $this->assertCount(12, $page->monthsList);

        // Test month filtering toggle
        $page->filterByMonth(5);
        $this->assertEquals('5', $page->selectedMonth);

        $data = $page->financialData;
        $this->assertIsArray($data);
        $this->assertEquals(5, $data['selected_month']);
        $this->assertEquals('Mai', $data['selected_month_name']);

        // Toggle off
        $page->filterByMonth(5);
        $this->assertEquals('', $page->selectedMonth);
    }
}
