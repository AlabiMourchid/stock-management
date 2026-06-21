<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu {{ $commande->numero }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            background: #fff;
            color: #000;
            padding: 20px;
        }
        .receipt {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-header h1 { font-size: 20px; font-family: sans-serif; font-weight: 900; }
        .receipt-header p  { font-size: 11px; line-height: 1.4; }
        .receipt-num {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        .lines { width: 100%; margin-bottom: 10px; }
        .lines tr td { padding: 3px 0; }
        .lines .nom { width: 55%; }
        .lines .qte { width: 15%; text-align: center; }
        .lines .pu  { width: 15%; text-align: right; font-size: 11px; }
        .lines .ss  { width: 15%; text-align: right; font-weight: bold; }
        .separator  { border: none; border-top: 1px dashed #000; margin: 8px 0; }
        .totals { width: 100%; }
        .totals td { padding: 3px 0; }
        .totals .label { font-weight: bold; }
        .totals .val   { text-align: right; font-weight: bold; }
        .totals .big   { font-size: 16px; }
        .receipt-footer {
            text-align: center;
            margin-top: 14px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 11px;
            line-height: 1.8;
        }
        .badge-paiement {
            display: inline-block;
            border: 1px solid #000;
            padding: 1px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin: 4px 0;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="receipt">

    <div class="receipt-header">
        <div style="font-size:28px;margin-bottom:4px">🍗</div>
        <h1>Amira a fait le poulet</h1>
        <p>Votre rôtisserie préférée<br>
            Tél : +229 01 67 03 50 44<br>
            Cotonou, Bénin</p>
    </div>

    <div class="receipt-num">
        <strong>{{ $commande->numero }}</strong><br>
        {{ $commande->created_at->format('d/m/Y à H:i') }}<br>
        Caissier : {{ $commande->user->name }}<br>
        {{ $commande->type === 'emporter' ? '📦 À emporter' : '🪑 Sur place' }}
    </div>

    <table class="lines">
        <thead>
        <tr>
            <td class="nom"><strong>Article</strong></td>
            <td class="qte"><strong>Qté</strong></td>
            <td class="pu"><strong>P.U.</strong></td>
            <td class="ss"><strong>S/T</strong></td>
        </tr>
        </thead>
        <tbody>
        @foreach($commande->lignes as $ligne)
            <tr>
                <td class="nom">{{ $ligne->produit->nom }}</td>
                <td class="qte">{{ $ligne->quantite }}</td>
                <td class="pu">{{ number_format($ligne->prix_unitaire, 0) }}</td>
                <td class="ss">{{ number_format($ligne->sous_total, 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <hr class="separator">

    <table class="totals">
        <tr>
            <td class="label">TOTAL TTC</td>
            <td class="val big">{{ number_format($commande->total_ttc, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($commande->montant_recu)
            <tr>
                <td class="label">Reçu</td>
                <td class="val">{{ number_format($commande->montant_recu, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($commande->monnaie_rendue > 0)
                <tr>
                    <td class="label">Monnaie</td>
                    <td class="val">{{ number_format($commande->monnaie_rendue, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endif
        @endif
        <tr>
            <td colspan="2">
                <span class="badge-paiement">
                    {{ match($commande->mode_paiement) {
                        'especes'      => '💵 Espèces',
                        'mobile_money' => '📱 Mobile Money',
                        'carte'        => '💳 Carte',
                        default        => $commande->mode_paiement,
                    } }}
                </span>
            </td>
        </tr>
    </table>

    @if($commande->notes)
        <div style="margin-top:8px;font-size:11px;font-style:italic">
            📝 {{ $commande->notes }}
        </div>
    @endif

    <div class="receipt-footer">
        ★ Merci pour votre visite ! ★<br>
        Revenez nous voir bientôt 🙂<br>
        <small style="opacity:.6">Conservez ce reçu</small>
    </div>

    <div class="no-print" style="margin-top:20px;text-align:center;display:flex;gap:10px;justify-content:center">
        <button onclick="window.print()" style="padding:8px 20px;background:#F4621F;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()" style="padding:8px 20px;background:#eee;border:none;border-radius:8px;cursor:pointer;font-size:14px">
            Fermer
        </button>
    </div>

</div>

<script>
    // Auto-print à l'ouverture si appelé depuis le POS
    if (window.opener) {
        setTimeout(() => window.print(), 400);
    }
</script>

</body>
</html>
