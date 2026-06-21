{{-- resources/views/admin/_article_menu_form.blade.php --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Nom de l'article <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control" placeholder="ex: Poulet entier rôti" required>
    </div>
    <div class="col-12">
        <label class="form-label">Catégorie <span class="text-danger">*</span></label>
        <select name="categorie_id" class="form-select" required>
            <option value="">Choisir</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Prix de vente (FCFA) <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" name="prix_vente" class="form-control" min="0" step="50" placeholder="0" required>
            <span class="input-group-text">FCFA</span>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description <span class="text-muted fw-normal">(optionnel)</span></label>
        <textarea name="description" class="form-control" rows="2" placeholder="Brève description affichée au client…"></textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="disponible" id="creer-disponible" value="1" checked>
            <label class="form-check-label" for="creer-disponible" style="font-size:13px">Visible dans le menu dès la création</label>
        </div>
    </div>
</div>
