<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de Scolarité - {{ $student->nom }} {{ $student->prenom }}</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            line-height: 1.6;
            font-size: 13px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #064e3b;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 900;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 4px;
        }
        .logo-sub {
            font-size: 11px;
            font-weight: 600;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .school-coords {
            font-size: 10px;
            color: #64748b;
            line-height: 1.4;
        }
        .ref-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 11px;
            text-align: right;
            vertical-align: middle;
        }
        .doc-title-container {
            text-align: center;
            margin: 35px 0;
        }
        .doc-title {
            display: inline-block;
            font-size: 22px;
            font-weight: 900;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 2px solid #047857;
            padding-bottom: 6px;
        }
        .statement-intro {
            font-size: 14px;
            margin-bottom: 25px;
            color: #334155;
        }
        .student-card {
            background-color: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0 30px 0;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-table td {
            padding: 6px 4px;
            font-size: 13px;
        }
        .student-label {
            font-weight: bold;
            color: #064e3b;
            width: 32%;
        }
        .student-value {
            color: #0f172a;
            font-weight: 600;
        }
        .body-paragraph {
            font-size: 13.5px;
            line-height: 1.7;
            color: #334155;
            margin-bottom: 20px;
            text-align: justify;
        }
        .footer-table {
            width: 100%;
            margin-top: 45px;
        }
        .date-location {
            font-size: 12px;
            color: #475569;
            font-weight: 600;
            vertical-align: top;
        }
        .stamp-box {
            text-align: center;
            width: 220px;
            vertical-align: top;
        }
        .stamp-circle {
            width: 110px;
            height: 110px;
            border: 2px dashed #047857;
            border-radius: 50%;
            margin: 0 auto;
            text-align: center;
            color: #047857;
            font-size: 9px;
            font-weight: bold;
            padding-top: 28px;
            box-sizing: border-box;
            line-height: 1.3;
            text-transform: uppercase;
        }
        .signature-line {
            margin-top: 55px;
            border-top: 1.5px solid #064e3b;
            padding-top: 5px;
            font-size: 11px;
            font-weight: bold;
            color: #064e3b;
            text-align: center;
        }
        .anti-fraud {
            margin-top: 60px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <div class="logo-title">CLASSY-ONE ACADEMY</div>
                <div class="logo-sub">Complexe Scolaire &amp; Éducatif Privé d'Excellence</div>
                <div class="school-coords">
                    Autorisation Ministérielle N° 4589/MEN<br>
                    Boulevard Al Qods, Quartier Californie, Casablanca - Maroc<br>
                    Tél : +212 5 22 80 90 00 | Email : scolarite@classy-one.ma
                </div>
            </td>
            <td style="width: 230px; vertical-align: top;">
                <div class="ref-box">
                    <strong>RÉF. VÉRIFICATION :</strong><br>
                    <span style="font-family: monospace; font-size: 13px; color: #047857; font-weight: bold;">
                        {{ $docRequest->reference ?? ('DOC-' . date('Y') . '-' . str_pad($docRequest->id ?? 1, 5, '0', STR_PAD_LEFT)) }}
                    </span><br>
                    <span style="color: #64748b; font-size: 10px;">
                        Délivré le : {{ date('d/m/Y') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div class="doc-title-container">
        <div class="doc-title">ATTESTATION DE SCOLARITÉ</div>
    </div>

    <!-- Main Content -->
    <div class="statement-intro">
        Le Directeur Général du complexe scolaire <strong>CLASSY-ONE ACADEMY</strong> certifie par la présente que :
    </div>

    <!-- Student Details Card -->
    <div class="student-card">
        <table class="student-table">
            <tr>
                <td class="student-label">Nom &amp; Prénom :</td>
                <td class="student-value">{{ strtoupper($student->nom) }} {{ ucfirst($student->prenom) }}</td>
            </tr>
            <tr>
                <td class="student-label">Matricule Scolaire :</td>
                <td class="student-value" style="font-family: monospace;">{{ $student->matricule }}</td>
            </tr>
            <tr>
                <td class="student-label">Date de Naissance :</td>
                <td class="student-value">{{ $student->dateNaissance ?? 'Non renseignée' }}</td>
            </tr>
            <tr>
                <td class="student-label">Classe Fréquentée :</td>
                <td class="student-value">
                    <strong>{{ $registre->classe->nomClasse ?? ($student->registres->first()?->classe->nomClasse ?? 'Classe assignée') }}</strong>
                </td>
            </tr>
            <tr>
                <td class="student-label">Année Scolaire :</td>
                <td class="student-value">
                    {{ $registre->classe->anneescolaire->nomAnnee ?? '2025/2026' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="body-paragraph">
        Est régulièrement inscrit(e) sur les registres de notre établissement d'enseignement privé et poursuit régulièrement ses études au titre de l'année scolaire en cours.
    </div>

    <div class="body-paragraph">
        La présente attestation est délivrée à la demande de l'intéressé(e) ou de son représentant légal pour servir et valoir ce que de droit.
    </div>

    <!-- Signatures and Stamp -->
    <table class="footer-table">
        <tr>
            <td class="date-location">
                Fait à Casablanca, le {{ date('d/m/Y') }}<br>
                <span style="font-size: 10px; color: #94a3b8; font-weight: normal;">
                    Mention : Inscription Validée &amp; À jour
                </span>
            </td>
            <td class="stamp-box">
                <div class="stamp-circle">
                    CLASSY-ONE<br>
                    ACADEMY<br>
                    ★ DIRECTION ★<br>
                    GÉNÉRALE
                </div>
                <div class="signature-line">
                    Le Secrétariat Général<br>
                    &amp; Direction Pédagogique
                </div>
            </td>
        </tr>
    </table>

    <!-- Anti-Fraud Security Notice -->
    <div class="anti-fraud">
        Document officiel signé et certifié électroniquement par le système de gestion intégrée Classy-One.<br>
        Toute falsification, surcharge ou altération du présent certificat expose son auteur à des poursuites judiciaires conformément aux lois en vigueur.
    </div>

</body>
</html>
