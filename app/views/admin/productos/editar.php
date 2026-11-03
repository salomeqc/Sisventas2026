<?php require  __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-3">
    <h3>Editar Producto</h3>
    <form action="<?= BASE_URL ?>/admin/productos/actualizar" method="post">
        <input type="hidden" name="id" value="<?= $producto['id'] ?>">
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="categoria">Categoría :</label>
                <select name="categoria" id="categoria" class="form-control">
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?php if ($categoria['id'] == $producto['categoria_id']) { echo "selected";} ?>><?= $categoria['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label for="codigo">Código :</label>
                <input type="text" class="form-control" name="codigo" id="codigo" maxlength="50" value="<?= htmlspecialchars($producto['codigo_sku']) ?>" required>
            </div>
            <div class="col-md-5 form-group">
                <label for="nombre">Nombre :</label>
                <input type="text" class="form-control" name="nombre" id="nombre" maxlength="150" value="<?= $producto['nombre'] ?>" required>
            </div>
            <div class="col-md-8 form-group">
                <label for="descripcion">Descripción :</label>
                <textarea name="descripcion" id="descripcion" required class="form-control" maxlength="500" rows="3"><?= $producto['descripcion'] ?></textarea>
            </div>
            <div class="col-md-2 form-group">
                <label for="precio">Precio :</label>
                <input type="number" class="form-control" name="precio" id="precio" min="0" step="0.01" value="<?= $producto['precio_venta'] ?>" required>
            </div>
            <div class="col-md-2 form-group">
                <label for="stock">Stock :</label>
                <input type="number" class="form-control" name="stock" id="stock" min="1" value="<?= $producto['stock_actual'] ?>" required value="1">
            </div>
            <div class="col-md-2 form-group">
                <label for="estado">Estado :</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="1" <?php if ($producto['activo'] == 1) {echo "selected";} ?>>Activo</option>
                    <option value="0" <?php if ($producto['activo'] == 0) {echo "selected";} ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="text-center">
            <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/productos"><i class="fas fa-times"></i> Cancelar</a>
            <button class="btn btn-info" type="reset"><i class="fas fa-trash"></i> Limpiar</button>
            <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Actualizar</button>
        </div>
    </form>
</div>
<?php require  __DIR__ . '/../../layouts/footer.php'; ?>