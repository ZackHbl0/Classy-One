<?php

namespace Tests\Feature\Documents;

use Tests\TestCase;
use App\Models\Student;
use App\Models\SchoolParent;
use App\Models\User;
use App\Models\DocumentRequest;
use App\Models\Classe;
use App\Models\Registre;
use App\Services\OfficialDocumentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class OfficialDocumentsModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected Student $studentA;
    protected Student $studentB;
    protected SchoolParent $parentA;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $students = Student::take(2)->get();
        if ($students->count() < 2) {
            $classe = Classe::first();
            $this->studentA = Student::create([
                'matricule' => 'STU-TEST-001',
                'nom' => 'El Idrissi',
                'prenom' => 'Amine',
                'password' => bcrypt('password123'),
                'telephone' => '0612345678',
            ]);
            $this->studentB = Student::create([
                'matricule' => 'STU-TEST-002',
                'nom' => 'Benali',
                'prenom' => 'Fatima',
                'password' => bcrypt('password123'),
                'telephone' => '0698765432',
            ]);
            if ($classe) {
                Registre::create(['idStudent' => $this->studentA->idStudent, 'Cla_id' => $classe->id]);
                Registre::create(['idStudent' => $this->studentB->idStudent, 'Cla_id' => $classe->id]);
            }
        } else {
            $this->studentA = $students[0];
            $this->studentB = $students[1];
        }

        // Ensure Registre exists for studentA
        if (!Registre::where('idStudent', $this->studentA->idStudent)->exists()) {
            $classe = Classe::first();
            if ($classe) {
                Registre::create(['idStudent' => $this->studentA->idStudent, 'Cla_id' => $classe->id]);
            }
        }

        // Parent linked to Student A
        $this->parentA = SchoolParent::first();
        if (!$this->parentA) {
            $this->parentA = SchoolParent::create([
                'name' => 'Parent Test',
                'email' => 'parent.test@classy-one.ma',
                'password' => bcrypt('password123'),
                'phone' => '0600000001',
            ]);
        }

        // Ensure parent_student link exists for studentA
        DB::table('parent_student')->updateOrInsert([
            'parent_id' => $this->parentA->id,
            'student_id' => $this->studentA->idStudent,
        ]);

        // Ensure parent is NOT linked to studentB for IDOR testing
        DB::table('parent_student')
            ->where('parent_id', $this->parentA->id)
            ->where('student_id', $this->studentB->idStudent)
            ->delete();

        // Admin User
        $this->adminUser = User::where('role', 'admin')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::create([
                'name' => 'Admin Directrice',
                'email' => 'admin.test@classy-one.ma',
                'password' => bcrypt('admin12345'),
                'role' => 'admin',
            ]);
        }
    }

    /**
     * Test 1: Available official document types can be listed.
     */
    public function test_available_document_types_can_be_retrieved(): void
    {
        $response = $this->getJson('/api/documents/types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'success',
            ])
            ->assertJsonFragment(['id' => 'attestation_scolarite'])
            ->assertJsonFragment(['id' => 'releve_notes'])
            ->assertJsonFragment(['id' => 'certificat_presence']);
    }

    /**
     * Test 2: Student can submit an official document request.
     */
    public function test_student_can_submit_document_request(): void
    {
        $token = $this->studentA->createToken('stu_token', ['role:student'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/documents/requests', [
                'document_type' => 'attestation_scolarite',
                'reason' => 'Dossier de candidature visa études',
                'urgency' => 'normal',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status' => 'success',
            ])
            ->assertJsonPath('data.document_type', 'attestation_scolarite')
            ->assertJsonPath('data.document_type_title', 'Attestation de Scolarité')
            ->assertJsonPath('data.status', 'Nouveau');

        $this->assertNotEmpty($response->json('data.reference'));
        $this->assertStringStartsWith('DOC-', $response->json('data.reference'));

        $this->assertDatabaseHas('document_requests', [
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
        ]);
    }

    /**
     * Test 3: Parent can submit document request for their linked child.
     */
    public function test_parent_can_submit_document_request_for_linked_child(): void
    {
        $token = $this->parentA->createToken('parent_token', ['role:parent'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/parent/documents', [
                'student_id' => $this->studentA->idStudent,
                'document_type' => 'certificat_presence',
                'reason' => 'Attestation pour organisme de mutuelle',
                'urgency' => 'urgent',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonPath('data.document_type', 'certificat_presence')
            ->assertJsonPath('data.document_type_title', 'Certificat de Présence');

        $this->assertDatabaseHas('document_requests', [
            'idStudent' => $this->studentA->idStudent,
            'parent_id' => $this->parentA->id,
            'document_type' => 'certificat_presence',
        ]);
    }

    /**
     * Test 4: Parent cannot submit document request for an unlinked student (IDOR prevention).
     */
    public function test_parent_cannot_submit_request_for_unlinked_child(): void
    {
        $token = $this->parentA->createToken('parent_token', ['role:parent'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/parent/documents', [
                'student_id' => $this->studentB->idStudent,
                'document_type' => 'attestation_scolarite',
                'reason' => 'Tentative frauduleuse',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => "Vous n'êtes pas autorisé à faire une demande pour cet élève.",
            ]);
    }

    /**
     * Test 5: Student can list their document requests with real-time status.
     */
    public function test_student_can_list_their_document_requests(): void
    {
        DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
            'reason' => 'Dossier administratif',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        $token = $this->studentA->createToken('stu_token', ['role:student'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/documents/requests');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'success',
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($this->studentA->idStudent, $data[0]['idStudent']);
        $this->assertNotNull($data[0]['reference']);
        $this->assertEquals('Nouveau', $data[0]['status']);
    }

    /**
     * Test 6: OfficialDocumentService generates Attestation de Scolarité PDF and sets status to 'pret'.
     */
    public function test_official_document_service_generates_attestation_pdf(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
            'reason' => 'Inscription sport',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        $service = app(OfficialDocumentService::class);
        $savedPath = $service->generate($docRequest, $this->adminUser);

        $this->assertNotEmpty($savedPath);
        $this->assertTrue(Storage::disk('local')->exists($savedPath) || Storage::disk('public')->exists($savedPath));

        $docRequest->refresh();
        $this->assertEquals(DocumentRequest::STATUS_PRET, $docRequest->status);
        $this->assertTrue($docRequest->isReady());
        $this->assertNotNull($docRequest->ready_date);
        $this->assertEquals($this->adminUser->id, $docRequest->processed_by);
        $this->assertNotNull($docRequest->reference);
    }

    /**
     * Test 7: OfficialDocumentService generates Certificat de Présence PDF.
     */
    public function test_official_document_service_generates_presence_pdf(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'certificat_presence',
            'reason' => 'Justificatif de transport',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        $service = app(OfficialDocumentService::class);
        $savedPath = $service->generate($docRequest, $this->adminUser);

        $this->assertTrue(Storage::disk('local')->exists($savedPath) || Storage::disk('public')->exists($savedPath));

        $docRequest->refresh();
        $this->assertEquals(DocumentRequest::STATUS_PRET, $docRequest->status);
    }

    /**
     * Test 8: OfficialDocumentService rejection workflow.
     */
    public function test_official_document_service_can_reject_request_with_reason(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        $service = app(OfficialDocumentService::class);
        $service->reject($docRequest, 'Dossier administratif incomplet : certificat médical manquant', $this->adminUser);

        $docRequest->refresh();
        $this->assertEquals(DocumentRequest::STATUS_REJETE, $docRequest->status);
        $this->assertTrue($docRequest->isRejected());
        $this->assertStringContainsString('certificat médical manquant', $docRequest->rejection_reason);
    }

    /**
     * Test 9: Authorized student can download ready document via API.
     */
    public function test_authorized_student_can_download_ready_document(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        // Generate the PDF
        app(OfficialDocumentService::class)->generate($docRequest, $this->adminUser);
        $docRequest->refresh();

        $token = $this->studentA->createToken('stu_token', ['role:student'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get("/api/documents/{$docRequest->id}/download");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Test 10: Unauthorized student cannot download other student's document (IDOR -> 403).
     */
    public function test_unauthorized_student_cannot_download_other_student_document(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentB->idStudent,
            'document_type' => 'attestation_scolarite',
            'status' => DocumentRequest::STATUS_PRET,
            'file_url' => 'documents/official/sample.pdf',
            'request_date' => now(),
        ]);

        // Student A tries to download Student B's document
        $tokenA = $this->studentA->createToken('stu_token_a', ['role:student'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson("/api/documents/{$docRequest->id}/download");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'êtes pas autorisé à consulter ce document.',
            ]);
    }

    /**
     * Test 11: HMAC signed temporary download URL works for ready documents.
     */
    public function test_temporary_signed_hmac_download_url_serves_ready_document(): void
    {
        $docRequest = DocumentRequest::create([
            'idStudent' => $this->studentA->idStudent,
            'document_type' => 'attestation_scolarite',
            'status' => DocumentRequest::STATUS_NOUVEAU,
            'request_date' => now(),
        ]);

        app(OfficialDocumentService::class)->generate($docRequest, $this->adminUser);
        $docRequest->refresh();

        // Generate valid HMAC signed URL
        $signedUrl = URL::temporarySignedRoute(
            'documents.signed_download',
            now()->addHour(),
            ['id' => $docRequest->id]
        );

        $response = $this->actingAs($this->studentA, 'sanctum')->get($signedUrl);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
