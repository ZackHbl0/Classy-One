<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Scolarité</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid #203B68;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #203B68;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .school-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            margin: 40px 0;
            text-transform: uppercase;
        }
        .content {
            margin-bottom: 50px;
            font-size: 16px;
        }
        .content p {
            margin: 15px 0;
        }
        .footer {
            margin-top: 80px;
        }
        .signature-box {
            float: right;
            text-align: center;
            width: 250px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .date-location {
            margin-bottom: 20px;
        }
        .stamp {
            margin-top: 40px;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">OMNIA ACADEMY</div>
        <div class="school-info">
            Complexe Scolaire Privé - Excellence & Innovation<br>
            Tél: +212 5XX XX XX XX | Email: contact@omnia-academy.ma<br>
            Rabat, Maroc
        </div>
    </div>

    <div class="title">Certificat de Scolarité</div>

    <div class="content">
        <p>Le Directeur de l'établissement <strong>OMNIA ACADEMY</strong> certifie que :</p>
        
        <p>L'étudiant(e) : <strong>{{ $student->nom }} {{ $student->prenom }}</strong></p>
        <p>Né(e) le : <strong>{{ $student->dateNaissance ?? 'N/A' }}</strong></p>
        <p>Matricule : <strong>{{ $student->matricule }}</strong></p>
        
        <p>Est régulièrement inscrit(e) au titre de l'année scolaire <strong>2025/2026</strong>.</p>
        
        <p>Niveau d'étude : <strong>{{ $student->niveau ?? 'N/A' }}</strong></p>
        <p>Filière : <strong>{{ $student->filiere ?? 'N/A' }}</strong></p>
        <p>Classe : <strong>{{ $registre->classe->nom ?? 'N/A' }}</strong></p>
    </div>

    <div class="footer">
        <div class="date-location">
            Fait à Rabat, le {{ date('d/m/Y') }}
        </div>
        
        <div class="signature-box">
            <strong>Le Directeur de l'Établissement</strong>
            <div class="signature-line">
                Cachet et Signature
            </div>
        </div>
    </div>

    <div style="clear: both;"></div>
    
    <div class="stamp">
        <p style="font-size: 10px; color: #999; margin-top: 100px;">
            Document généré électroniquement par le portail Omnia Academy.<br>
            Toute rature ou surcharge annule la validité du présent document.
        </p>
    </div>
</body>
</html>
