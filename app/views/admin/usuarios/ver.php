<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-3">
    <h3>Detalle de Usuario</h3>
    <div class="row">
        <div class="col-md-4 form-group">
            <label>Rol :</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['rol_nombre']) ?>" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Nombre :</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Apellidos :</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['apellidos']) ?>" readonly>
        </div>
        <div class="col-md-5 form-group">
            <label>Email :</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Teléfono :</label>
            <input type="text" class="form-control" value="<?= !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : 'No registrado' ?>" readonly>
        </div>
        <div class="col-md-3 form-group">
            <label>Estado :</label>
            <input type="text" class="form-control" value="<?= ($usuario['activo'] == 1) ? 'Activo' : 'Inactivo' ?>" readonly>
        </div>
        <div class="col-md-6 form-group">
            <label>Fecha de Registro :</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['fecha_registro']) ?>" readonly>
        </div>
        <div class="col-md-6 form-group">
            <label>Total de Ventas / Compras :</label>
            <input type="text" class="form-control" value="<?= (int)($usuario['total_ventas'] ?? 0) ?>" readonly>
        </div>
    </div>
    <div class="text-center">
        <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/usuarios"><i class="fas fa-times"></i> Volver</a>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/usuarios/editar/<?= $usuario['id'] ?>"><i class="fa fa-edit"></i> Editar</a>
        <a class="btn btn-danger" href="<?= BASE_URL ?>/admin/usuarios/eliminar/<?= $usuario['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');" data-sigi-confirm><i class="fa fa-trash"></i> Eliminar</a>
    </div>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
