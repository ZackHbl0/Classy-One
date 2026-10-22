<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement — {{ $paiement->reference }}</title>
    <style>
        @page {
            margin: 25px 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .school-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }
        .school-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }
        .receipt-badge-box {
            text-align: right;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: 800;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .receipt-ref {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
            border: 1px solid #cbd5e1;
        }
        .receipt-date {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .cards-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            vertical-align: top;
            width: 50%;
        }
        .card-header {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .info-row {
            margin-bottom: 4px;
            font-size: 12px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            display: inline-block;
            width: 90px;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .payment-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
        }
        .payment-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .payment-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .amount-col {
            text-align: right;
            font-size: 14px;
            font-weight: 800;
            color: #047857;
        }

        .summary-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 25px;
        }
        .summary-table {
            width: 100%;
            font-size: 12px;
        }
        .summary-table td {
            padding: 3px 0;
        }
        .summary-label {
            color: #334155;
            font-weight: 600;
        }
        .summary-val {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .summary-highlight {
            border-top: 1px dashed #86efac;
            padding-top: 6px !important;
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
        }

        .signatures-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-title {
            font-size: 12px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 60px;
        }
        .signature-line {
            display: inline-block;
            width: 180px;
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            font-size: 11px;
            color: #64748b;
        }

        .footer-note {
            margin-top: 35px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
        .security-stamp {
            display: inline-block;
            border: 2px dashed #059669;
            color: #059669;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <div class="school-title">Classy-One</div>
                <div class="school-subtitle">
                    {{ $school_name }}<br>
                    {{ $school_address }} | Tél : {{ $school_phone }}
                </div>
            </td>
            <td class="receipt-badge-box" style="vertical-align: middle;">
                <div class="receipt-title">Reçu de Paiement</div>
                <div class="receipt-ref">{{ $paiement->reference }}</div>
                <div class="receipt-date">Émis le : {{ $paiement->created_at ? $paiement->created_at->format('d/m/Y à H:i') : $date_impression }}</div>
            </td>
        </tr>
    </table>

    <!-- Student & Payment Method Cards -->
    <table class="cards-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-card">
                <div class="card-header">Informations Élève</div>
                <div class="info-row"><span class="info-label">Élève :</span> <span class="info-value">{{ $student?->full_name ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Matricule :</span> <span class="info-value">{{ $student?->matricule ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Classe :</span> <span class="info-value">{{ $classe?->nom ?? 'Non assignée' }}</span></div>
                @if($student?->tuteur_name || $student?->numero_tuteur)
                <div class="info-row"><span class="info-label">Tuteur :</span> <span class="info-value">{{ $student?->tuteur_name ?? 'Tuteur' }} ({{ $student?->numero_tuteur }})</span></div>
                @endif
            </td>
            <td class="info-card">
                <div class="card-header">Détails Règlement</div>
                <div class="info-row"><span class="info-label">Mode :</span> <span class="info-value">{{ $paiement->modePaiement ?? 'Espèce' }}</span></div>
                <div class="info-row"><span class="info-label">Statut :</span> <span class="info-value" style="color: #047857;">{{ $paiement->statut }}</span></div>
                <div class="info-row"><span class="info-label">Échéance :</span> <span class="info-value">{{ $paiement->dateEcheance ? $paiement->dateEcheance->format('d/m/Y') : 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Opérateur :</span> <span class="info-value">{{ $staff?->name ?? 'Service Comptabilité' }}</span></div>
            </td>
        </tr>
    </table>

    <!-- Payment Breakdown Table -->
    <table class="payment-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 50%;">Désignation & Libellé</th>
                <th style="width: 25%;">Mode de Paiement</th>
                <th style="width: 25%; text-align: right;">Montant Réglé</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Frais de Scolarité & Enseignement</strong><br>
                    <span style="color: #64748b; font-size: 11px;">Réf transaction : {{ $paiement->reference }} | Échéance : {{ $paiement->dateEcheance ? $paiement->dateEcheance->format('d/m/Y') : '-' }}</span>
                    @if($paiement->notes)
                        <br><span style="color: #3b82f6; font-size: 11px; font-style: italic;">Note : {{ $paiement->notes }}</span>
                    @endif
                </td>
                <td>{{ $paiement->modePaiement ?? 'Espèce' }}</td>
                <td class="amount-col">{{ number_format((float) $paiement->montant, 2, ',', ' ') }} MAD</td>
            </tr>
        </tbody>
    </table>

    <!-- Student Tuition & Scholarship Synthesis -->
    <div class="summary-box">
        <div style="font-weight: bold; color: #166534; font-size: 12px; margin-bottom: 8px; text-transform: uppercase;">
            Situation Financière de l'Élève après ce Règlement
        </div>
        <table class="summary-table">
            <tr>
                <td class="summary-label">Tarif de base annuel :</td>
                <td class="summary-val">{{ number_format($student?->base_tuition ?? 15000, 2, ',', ' ') }} MAD</td>
            </tr>
            @if(($student?->remise_montant_total ?? 0) > 0)
            <tr>
                <td class="summary-label" style="color: #0284c7;">
                    Bourse / Remise accordée ({{ $student?->remise_label }}) :
                </td>
                <td class="summary-val" style="color: #0284c7;">
                    - {{ number_format($student?->remise_montant_total, 2, ',', ' ') }} MAD
                </td>
            </tr>
            @endif
            <tr>
                <td class="summary-label"><strong>Scolarité nette exigible :</strong></td>
                <td class="summary-val"><strong>{{ number_format($student?->frais_scolarite_total ?? 15000, 2, ',', ' ') }} MAD</strong></td>
            </tr>
            <tr>
                <td class="summary-label">Total des versements cumulés validés :</td>
                <td class="summary-val" style="color: #059669;">{{ number_format($student?->total_paye ?? 0, 2, ',', ' ') }} MAD</td>
            </tr>
            <tr>
                <td class="summary-label summary-highlight">Reste à payer actualisé :</td>
                <td class="summary-val summary-highlight" style="color: {{ ($student?->reste_a_payer ?? 0) > 0 ? '#b91c1c' : '#059669' }};">
                    {{ number_format($student?->reste_a_payer ?? 0, 2, ',', ' ') }} MAD
                </td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">Signature & Accord du Déposant</div>
                <div class="signature-line">Mention "Lu et approuvé"</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">Cachet & Signature de l'Administration</div>
                <div class="security-stamp">RÈGLEMENT CERTIFIÉ CONFORME</div>
                <br>
                <div class="signature-line" style="margin-top: 15px;">{{ $staff?->name ?? 'Direction Financière' }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-note">
        Ce reçu officiel fait foi de quittance de paiement pour le montant stipulé ci-dessus. Tout chèque est accepté sous réserve d'encaissement.<br>
        Classy-One Academy — Système Intégré de Gestion Scolaire. Document généré le {{ $date_impression }}
    </div>

</body>
</html>
