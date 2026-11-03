<?php require __DIR__ . '/../../layouts/header.php'; ?>
<div class="card p-2">
    <h3>Lista de Usuarios</h3>
    <br>
    <a href="<?= BASE_URL ?>/admin/usuarios/nuevo" class="btn btn-success col-2"> + Nuevo </a>
    <table class="table-bordered m-3">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Rol</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $contador = 0;
            foreach ($usuarios as $usuario) {
                $contador += 1;
            ?>
            <tr>
                <td><?php echo $contador; ?></td>
                <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['apellidos']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td><?php echo !empty($usuario['telefono']) ? htmlspecialchars($usuario['telefono']) : '-'; ?></td>
                <td><?php echo ($usuario['activo'] == 1) ? 'Activo' : 'Inactivo'; ?></td>
                <td>
                    <a class="btn btn-outline-info" title="Ver" href="<?= BASE_URL ?>/admin/usuarios/ver/<?= $usuario['id'] ?>"><i class="fa fa-eye"></i></a>
                    <a class="btn btn-outline-primary" title="Editar" href="<?= BASE_URL ?>/admin/usuarios/editar/<?= $usuario['id'] ?>"><i class="fa fa-edit"></i></a>
                    <a class="btn btn-outline-danger" title="Eliminar" href="<?= BASE_URL ?>/admin/usuarios/eliminar/<?= $usuario['id'] ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');" data-sigi-confirm><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../../layouts/footer.php'; ?>
