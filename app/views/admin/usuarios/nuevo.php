<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-3">
    <h3>Registrar Nuevo Usuario</h3>
    <form action="<?= BASE_URL ?>/admin/usuarios/guardar" method="POST">
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="rol_id">Rol :</label>
                <select name="rol_id" id="rol_id" class="form-control" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['id'] ?>" <?= (isset($usuario['rol_id']) && $usuario['rol_id'] == $rol['id']) ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label for="nombre">Nombre :</label>
                <input type="text" name="nombre" id="nombre" class="form-control" maxlength="40" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="apellidos">Apellidos :</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" maxlength="80" value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>" required>
            </div>
            <div class="col-md-5 form-group">
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" class="form-control" maxlength="150" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="telefono">Teléfono :</label>
                <input type="text" name="telefono" id="telefono" class="form-control" maxlength="20" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-3 form-group">
                <label for="activo">Estado :</label>
                <select name="activo" id="activo" class="form-control">
                    <option value="1" <?= (!isset($usuario['activo']) || $usuario['activo'] == 1) ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (isset($usuario['activo']) && $usuario['activo'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="password">Contraseña :</label>
                <input type="password" name="password" id="password" class="form-control" minlength="6" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="confirm_password">Confirmar Contraseña :</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" required>
            </div>
        </div>
        <div class="text-center">
            <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/usuarios"><i class="fas fa-times"></i> Cancelar</a>
            <button class="btn btn-info" type="reset"><i class="fas fa-trash"></i> Limpiar</button>
            <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Registrar</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
