{{-- resources/views/admin/_fournisseur_form.blade.php --}}
{{-- Partial partagé entre le modal Créer et le modal Éditer --}}

<div class="mb-3">
    <label class="form-label">Nom du fournisseur <span class="text-danger">*</span></label>
    <input type="text" name="nom" class="form-control"
           placeholder="ex: Ferme Avicom, Marché Dantokpa…" required>
</div>

<div class="mb-3">
    <label class="form-label">Téléphone <span class="text-muted fw-normal">(optionnel)</span></label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-telephone" style="font-size:14px"></i>
        </span>
        <input type="text" name="telephone" class="form-control"
               placeholder="+229 01 XX XX XX XX">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Email <span class="text-muted fw-normal">(optionnel)</span></label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-envelope" style="font-size:14px"></i>
        </span>
        <input type="email" name="email" class="form-control"
               placeholder="contact@fournisseur.com">
    </div>
</div>

<div class="mb-1">
    <label class="form-label">Adresse / localisation <span class="text-muted fw-normal">(optionnel)</span></label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-geo-alt" style="font-size:14px"></i>
        </span>
        <textarea name="adresse" class="form-control" rows="2"
                  placeholder="ex: Quartier Cadjèhoun, Cotonou…"></textarea>
    </div>
</div>
