<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-3">
    <h3>Editar Usuario</h3>
    <form action="<?= BASE_URL ?>/admin/usuarios/actualizar" method="post">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="rol">Rol :</label>
                <select name="rol" id="rol" class="form-control">
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?php if ($rol['id'] == $usuario['rol_id']) { echo "selected"; } ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label for="nombre">Nombre :</label>
                <input type="text" class="form-control" name="nombre" id="nombre" maxlength="40" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="apellidos">Apellidos :</label>
                <input type="text" class="form-control" name="apellidos" id="apellidos" maxlength="80" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
            </div>
            <div class="col-md-5 form-group">
                <label for="email">Email :</label>
                <input type="email" class="form-control" name="email" id="email" maxlength="150" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="telefono">Teléfono :</label>
                <input type="text" class="form-control" name="telefono" id="telefono" maxlength="20" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-3 form-group">
                <label for="estado">Estado :</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="1" <?php if ($usuario['activo'] == 1) { echo "selected"; } ?>>Activo</option>
                    <option value="0" <?php if ($usuario['activo'] == 0) { echo "selected"; } ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="password">Nueva Contraseña :</label>
                <input type="password" class="form-control" name="password" id="password" minlength="6" placeholder="Dejar en blanco para conservar actual">
            </div>
            <div class="col-md-6 form-group">
                <label for="confirm_password">Confirmar Nueva Contraseña :</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" minlength="6" placeholder="Repite la nueva contraseña">
            </div>
        </div>
        <div class="text-center">
            <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/usuarios"><i class="fas fa-times"></i> Cancelar</a>
            <button class="btn btn-info" type="reset"><i class="fas fa-trash"></i> Limpiar</button>
            <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Actualizar</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>

