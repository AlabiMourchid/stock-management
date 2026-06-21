
<div class="modal-header">
    <h5 class="modal-title">
        @if($produit)
            <i class="bi bi-pencil text-warning me-2"></i>Modifier le produit
        @else
            <i class="bi bi-plus-circle text-success me-2"></i>Nouveau produit
        @endif
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="row g-3">

        <div class="col-12  ">
            <label class="form-label">Nom du produit <span class="text-danger">*</span></label>
            <input type="text" name="nom" class="form-control"

                   placeholder="ex: Poulet entier rôti" required>
        </div>

        <div class="col-12">
            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
            <select name="categorie_id" class="form-select" required>
                <option value="">— Choisir —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('categorie_id', $produit?->categorie_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6">
            <label class="form-label">Prix de vente (FCFA) <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" name="prix_vente" class="form-control"
                       value="{{ old('prix_vente', $produit?->prix_vente) }}"
                       min="0" step="50" placeholder="0" required>
                <span class="input-group-text">FCFA</span>
            </div>
        </div>

        <div class="col-6">
            <label class="form-label">Coût unitaire (FCFA)</label>
            <div class="input-group">
                <input type="number" name="cout_unitaire" class="form-control"
                       value="{{ old('cout_unitaire', $produit?->cout_unitaire ?? 0) }}"
                       min="0" step="50" placeholder="0">
                <span class="input-group-text">FCFA</span>
            </div>
            <div class="form-text">Utilisé pour le calcul du coût des pertes</div>
        </div>

        <div class="col-6">
            <label class="form-label">Unité <span class="text-danger">*</span></label>
            <input type="text" name="unite" class="form-control"
                   value="{{ old('unite', $produit?->unite ?? 'pièce') }}"
                   placeholder="pièce, kg, litre, sachet…" required>
        </div>

        <div class="col-6">
            <label class="form-label">Seuil d'alerte critique <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" name="seuil_critique" class="form-control"
                       value="{{ old('seuil_critique', $produit?->seuil_critique ?? 5) }}"
                       min="0" step="0.5" required>
                <span class="input-group-text" id="unite-seuil">unité(s)</span>
            </div>
            <div class="form-text">En-dessous de ce seuil → badge rouge</div>
        </div>

        <div class="col-12">
            <div class="d-flex gap-4 pt-1">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           name="visible_pos" id="visible_pos_check" value="1"
                        {{ old('visible_pos', $produit?->visible_pos ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="visible_pos_check" style="font-size:13px">
                        Visible en caisse (POS)
                    </label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           name="actif" id="actif_check" value="1"
                        {{ old('actif', $produit?->actif ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="actif_check" style="font-size:13px">
                        Produit actif
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
