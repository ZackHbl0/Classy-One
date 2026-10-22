<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class VerifyAuditIntegrityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:verify-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie l\'intégrité cryptographique et la non-répudiation de la chaîne des journaux d\'audit.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Démarrage de la vérification de la chaîne de signature HMAC des Audit Logs...");

        $result = AuditLog::verifyChainIntegrity();

        if ($result['valid']) {
            $this->info("✓ Succès : {$result['message']}");
            $this->info("✓ Nombre de journaux vérifiés : {$result['verified_count']}");
            return Command::SUCCESS;
        }

        $this->error("✗ ALERTE SÉCURITÉ : Falsification ou altération détectée !");
        $this->error("✗ Détail : {$result['error']}");
        $this->error("✗ Identifiant corrompu : #{$result['broken_id']}");

        return Command::FAILURE;
    }
}
