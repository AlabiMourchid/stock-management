
<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control" placeholder="ex: Poulet entier cru" required>
    </div>
    <div class="col-6">
        <label class="form-label">Unité <span class="text-danger">*</span></label>
        <input type="text" name="unite" class="form-control" placeholder="pièce, kg, litre…" required>
    </div>
    <div class="col-6">
        <label class="form-label">Coût unitaire (FCFA)</label>
        <div class="input-group">
            <input type="number" name="cout_unitaire" class="form-control" min="0" step="50" placeholder="0">
            <span class="input-group-text">FCFA</span>
        </div>
    </div>
    <div class="col-6">
        <label class="form-label">Stock initial</label>
        <input type="number" name="stock_actuel" class="form-control" min="0" step="0.01" placeholder="0" value="0">
    </div>
    <div class="col-6">
        <label class="form-label">Seuil critique <span class="text-danger">*</span></label>
        <input type="number" name="seuil_critique" class="form-control" min="0" step="0.5" placeholder="5" required>
    </div>
</div>
