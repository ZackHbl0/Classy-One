<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin de Notes – {{ $student->nom }} {{ $student->prenom }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            padding: 30px 40px;
            line-height: 1.5;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid #203B68;
        }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; text-align: right; vertical-align: middle; }
        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #203B68;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .school-sub {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #203B68;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 11px;
            color: #888;
            margin-top: 2px;
        }

        /* Student Info Box */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #f4f7ff;
            border: 1px solid #c6d4f0;
            border-radius: 4px;
        }
        .info-table td {
            padding: 6px 12px;
            font-size: 11px;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #203B68;
            width: 30%;
        }

        /* Section heading */
        .section-title {
            background: #203B68;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        /* Grades Table */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grades-table th {
            background: #e8edf7;
            color: #203B68;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #c6d4f0;
        }
        .grades-table td {
            padding: 6px 10px;
            border: 1px solid #e0e6f5;
            font-size: 11px;
        }
        .grades-table tr:nth-child(even) { background: #f9fbff; }
        .grades-table tr:hover { background: #eef2fd; }
        .center { text-align: center; }
        .bold { font-weight: bold; }

        /* Grade badges */
        .badge-pass {
            display: inline-block;
            background: #22c55e;
            color: white;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-fail {
            display: inline-block;
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: bold;
        }
        .coeff-badge {
            display: inline-block;
            background: #f59e0b;
            color: white;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: bold;
        }

        /* Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        .summary-table td {
            padding: 8px 14px;
            border: 1px solid #c6d4f0;
            font-size: 12px;
        }
        .summary-table .label-cell {
            background: #e8edf7;
            color: #203B68;
            font-weight: bold;
            width: 40%;
        }
        .summary-table .value-cell {
            font-weight: bold;
            font-size: 14px;
            color: #203B68;
        }

        /* Mention */
        .mention-box {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 99px;
            font-weight: bold;
            font-size: 13px;
            color: white;
        }
        .mention-tres-bien  { background: #22c55e; }
        .mention-bien       { background: #3b82f6; }
        .mention-assez-bien { background: #8b5cf6; }
        .mention-passable   { background: #f59e0b; }
        .mention-insuffisant{ background: #ef4444; }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #c6d4f0;
        }
        .signature-row {
            display: table;
            width: 100%;
        }
        .signature-col {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signature-line {
            margin: 50px auto 0;
            border-top: 1px solid #333;
            width: 180px;
            padding-top: 6px;
            font-size: 11px;
            color: #555;
        }
        .generated-note {
            text-align: center;
            font-size: 9px;
            color: #aaa;
            margin-top: 20px;
        }

        /* Page break helpers */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="school-name">Omnia Academy</div>
            <div class="school-sub">Complexe Scolaire Privé – Excellence &amp; Innovation<br>
                Tél: +212 5XX XX XX XX | contact@omnia-academy.ma | Rabat, Maroc
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">Bulletin de Notes</div>
            <div class="doc-subtitle">Semestre : {{ $semester }}<br>
                Année scolaire : {{ $anneeScolaire ?? '2025/2026' }}
            </div>
        </div>
    </div>

    <!-- STUDENT INFO -->
    <table class="info-table">
        <tr>
            <td>Nom &amp; Prénom</td>
            <td><strong>{{ $student->nom }} {{ $student->prenom }}</strong></td>
            <td>Matricule</td>
            <td><strong>{{ $student->matricule }}</strong></td>
        </tr>
        <tr>
            <td>Classe</td>
            <td>{{ $classeNom ?? 'Non assignée' }}</td>
            <td>Date de naissance</td>
            <td>{{ $student->dateNaissance ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- GRADES TABLE -->
    <div class="section-title">Résultats par Matière</div>
    <table class="grades-table">
        <thead>
            <tr>
                <th>Matière</th>
                <th class="center">Coefficient</th>
                <th class="center">Moyenne / 20</th>
                <th class="center">Moy × Coeff</th>
                <th class="center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subjects as $subject)
                <tr>
                    <td class="bold">{{ $subject['subject_name'] }}</td>
                    <td class="center">
                        <span class="coeff-badge">{{ $subject['coefficient'] }}</span>
                    </td>
                    <td class="center bold">
                        {{ number_format($subject['subject_average'], 2) }}
                    </td>
                    <td class="center">
                        {{ number_format($subject['subject_average'] * $subject['coefficient'], 2) }}
                    </td>
                    <td class="center">
                        @if ($subject['is_passing'])
                            <span class="badge-pass">Admis</span>
                        @else
                            <span class="badge-fail">Non admis</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            @if (empty($subjects))
                <tr>
                    <td colspan="5" class="center" style="color:#999; padding:16px;">
                        Aucune note enregistrée pour ce semestre.
                    </td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background:#e8edf7; font-weight:bold;">
                <td>TOTAL</td>
                <td class="center">{{ $totalCoefficient }}</td>
                <td class="center">–</td>
                <td class="center">{{ number_format(array_sum(array_map(fn($s) => $s['subject_average'] * $s['coefficient'], $subjects)), 2) }}</td>
                <td class="center">–</td>
            </tr>
        </tfoot>
    </table>

    <!-- SUMMARY -->
    <table class="summary-table">
        <tr>
            <td class="label-cell">Moyenne Générale Pondérée</td>
            <td class="value-cell">{{ number_format($weightedAverage, 2) }} / 20</td>
            <td class="label-cell">Rang dans la classe</td>
            <td class="value-cell">{{ $rank ? "#{$rank}" : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Mention</td>
            <td class="value-cell" colspan="3">
                @php
                    $mentionClass = match(true) {
                        $weightedAverage >= 16 => 'mention-tres-bien',
                        $weightedAverage >= 14 => 'mention-bien',
                        $weightedAverage >= 12 => 'mention-assez-bien',
                        $weightedAverage >= 10 => 'mention-passable',
                        default                => 'mention-insuffisant',
                    };
                @endphp
                <span class="mention-box {{ $mentionClass }}">{{ $mention }}</span>
            </td>
        </tr>
    </table>

    <!-- FOOTER / SIGNATURES -->
    <div class="footer">
        <div class="signature-row">
            <div class="signature-col">
                <div class="signature-line">Le Directeur de l'Établissement</div>
            </div>
            <div class="signature-col">
                <div class="signature-line">Cachet &amp; Signature</div>
            </div>
        </div>
    </div>

    <p class="generated-note">
        Document généré électroniquement le {{ date('d/m/Y à H:i') }} par le portail Omnia Academy.<br>
        Toute rature ou surcharge annule la validité du présent document.
    </p>

</body>
</html>
