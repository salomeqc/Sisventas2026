<?php

namespace App\Controllers\Admin;

require_once __DIR__ . '/../../models/Admin/Usuarios.php';

use Core\Controller;
use Core\Auth;
use App\Models\Admin\Usuarios;

class UsuariosController extends Controller
{
    protected $usuariosModel;

    public function __construct()
    {
        parent::__construct();
        // Control de acceso para administradores
        if (!Auth::esAdmin()) {
            $_SESSION['flash_error'] = 'Acceso denegado. Se requieren permisos de administrador.';
            header('Location: ' . BASE_URL . '/tienda');
            exit;
        }

        $this->usuariosModel = new Usuarios();
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        $usuarios = $this->usuariosModel->listar();

        $this->view('admin/usuarios/index', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Gestión de Usuarios',
            'usuarios'  => $usuarios
        ]);
        exit;
    }

    /**
     * Muestra el formulario para registrar un nuevo usuario
     */
    public function nuevo()
    {
        $roles = $this->usuariosModel->obtenerRoles();
        $formData = $_SESSION['form_usuario'] ?? [];
        unset($_SESSION['form_usuario']);

        $this->view('admin/usuarios/nuevo', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Registrar Nuevo Usuario',
            'roles'     => $roles,
            'usuario'   => $formData
        ]);
        exit;
    }

    /**
     * Procesa el formulario de registro de usuario (POST)
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $data = $_POST;
        $nombre          = trim($data['nombre'] ?? '');
        $apellidos       = trim($data['apellidos'] ?? '');
        $email           = trim(strtolower($data['email'] ?? ''));
        $password        = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $rolId           = isset($data['rol_id']) ? (int)$data['rol_id'] : 0;
        $telefono        = trim($data['telefono'] ?? '');
        $activo          = isset($data['activo']) ? (int)$data['activo'] : 1;

        // Validaciones
        $errores = [];

        if (empty($nombre)) {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > 40) {
            $errores[] = 'El nombre no debe exceder 40 caracteres.';
        }

        if (empty($apellidos)) {
            $errores[] = 'Los apellidos son obligatorios.';
        } elseif (mb_strlen($apellidos) > 80) {
            $errores[] = 'Los apellidos no deben exceder 80 caracteres.';
        }

        if (empty($email)) {
            $errores[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        } elseif (mb_strlen($email) > 150) {
            $errores[] = 'El correo electrónico no debe exceder 150 caracteres.';
        } elseif ($this->usuariosModel->obtenerPorEmail($email)) {
            $errores[] = 'El correo electrónico ingresado ya se encuentra registrado por otro usuario.';
        }

        if (empty($password)) {
            $errores[] = 'La contraseña es obligatoria.';
        } elseif (strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($password !== $confirmPassword) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($rolId <= 0) {
            $errores[] = 'Debe seleccionar un rol válido.';
        }

        if (!empty($telefono) && mb_strlen($telefono) > 20) {
            $errores[] = 'El teléfono no debe exceder 20 caracteres.';
        }

        if (!empty($errores)) {
            $_SESSION['flash_error'] = implode('<br>', $errores);
            $_SESSION['form_usuario'] = $data;
            header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
            exit;
        }

        // Registrar usuario
        $resultado = $this->usuariosModel->registrar([
            'rol_id'   => $rolId,
            'nombre'   => $nombre,
            'apellidos'=> $apellidos,
            'email'    => $email,
            'password' => $password,
            'telefono' => $telefono,
            'activo'   => $activo
        ]);

        if ($resultado) {
            $_SESSION['flash_success'] = 'Usuario registrado exitosamente.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
        } else {
            $_SESSION['flash_error'] = 'Error al registrar el usuario en la base de datos.';
            $_SESSION['form_usuario'] = $data;
            header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
        }
        exit;
    }

    /**
     * Muestra el formulario para editar un usuario
     */
    public function editar($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de usuario inválido.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $usuario = $this->usuariosModel->ver($id);
        if (!$usuario) {
            $_SESSION['flash_error'] = 'El usuario solicitado no existe.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $roles = $this->usuariosModel->obtenerRoles();

        $this->view('admin/usuarios/editar', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Editar Usuario: ' . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']),
            'usuario'   => $usuario,
            'roles'     => $roles
        ]);
        exit;
    }

    /**
     * Procesa la actualización de un usuario (POST)
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $data = $_POST;
        $id              = isset($data['id']) ? (int)$data['id'] : 0;
        $nombre          = trim($data['nombre'] ?? '');
        $apellidos       = trim($data['apellidos'] ?? '');
        $email           = trim(strtolower($data['email'] ?? ''));
        $password        = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $rolId           = isset($data['rol_id']) ? (int)$data['rol_id'] : 0;
        $telefono        = trim($data['telefono'] ?? '');
        $activo          = isset($data['activo']) ? (int)$data['activo'] : 1;

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de usuario no válido.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $usuarioExistente = $this->usuariosModel->ver($id);
        if (!$usuarioExistente) {
            $_SESSION['flash_error'] = 'El usuario que intenta modificar no existe.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Validaciones
        $errores = [];

        if (empty($nombre)) {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > 40) {
            $errores[] = 'El nombre no debe exceder 40 caracteres.';
        }

        if (empty($apellidos)) {
            $errores[] = 'Los apellidos son obligatorios.';
        } elseif (mb_strlen($apellidos) > 80) {
            $errores[] = 'Los apellidos no deben exceder 80 caracteres.';
        }

        if (empty($email)) {
            $errores[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        } elseif (mb_strlen($email) > 150) {
            $errores[] = 'El correo electrónico no debe exceder 150 caracteres.';
        } else {
            // Verificar unicidad de correo excluyendo el usuario actual
            $otroUsuario = $this->usuariosModel->obtenerPorEmail($email, $id);
            if ($otroUsuario) {
                $errores[] = 'El correo electrónico ingresado ya está en uso por otro usuario.';
            }
        }

        if ($rolId <= 0) {
            $errores[] = 'Debe seleccionar un rol válido.';
        }

        // Si se ingresó contraseña, validar longitud y confirmación
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            } elseif ($password !== $confirmPassword) {
                $errores[] = 'Las nuevas contraseñas no coinciden.';
            }
        }

        if (!empty($telefono) && mb_strlen($telefono) > 20) {
            $errores[] = 'El teléfono no debe exceder 20 caracteres.';
        }

        // Regla de seguridad: Si el admin en sesión se edita a sí mismo, no puede desactivarse ni quitarse rol admin
        $usuarioSesionId = $_SESSION['user_id'] ?? 0;
        if ($id === (int)$usuarioSesionId) {
            if ($activo !== 1) {
                $errores[] = 'No puedes desactivar tu propia cuenta mientras estás en sesión activa.';
            }
            if ($rolId !== 1) {
                $errores[] = 'No puedes remover tu propio rol de Administrador mientras estás en sesión activa.';
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash_error'] = implode('<br>', $errores);
            header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . $id);
            exit;
        }

        // Actualizar usuario
        $resultado = $this->usuariosModel->actualizar([
            'id'       => $id,
            'rol_id'   => $rolId,
            'nombre'   => $nombre,
            'apellidos'=> $apellidos,
            'email'    => $email,
            'password' => $password,
            'telefono' => $telefono,
            'activo'   => $activo
        ]);

        if ($resultado) {
            // Actualizar nombre en sesión si el admin se editó a sí mismo
            if ($id === (int)$usuarioSesionId) {
                $_SESSION['user_name'] = $nombre . ' ' . $apellidos;
            }
            $_SESSION['flash_success'] = 'Usuario actualizado correctamente.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar los datos del usuario en la base de datos.';
            header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . $id);
        }
        exit;
    }

    /**
     * Consulta y visualiza el detalle completo de un usuario
     */
    public function consultar($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de usuario no válido.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $usuario = $this->usuariosModel->ver($id);
        if (!$usuario) {
            $_SESSION['flash_error'] = 'El usuario solicitado no existe.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $this->view('admin/usuarios/ver', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Detalle de Usuario',
            'usuario'   => $usuario
        ]);
        exit;
    }

    /**
     * Alias de consultar para compatibilidad con rutas /ver/{id}
     */
    public function ver($id = null)
    {
        $this->consultar($id);
    }

    /**
     * Elimina un usuario del sistema (con protecciones de integridad referencial)
     */
    public function eliminar($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de usuario no válido.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Protección 1: No autoeliminarse
        $usuarioSesionId = $_SESSION['user_id'] ?? 0;
        if ($id === (int)$usuarioSesionId) {
            $_SESSION['flash_error'] = 'No puedes eliminar tu propia cuenta de usuario.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Verificar existencia
        $usuario = $this->usuariosModel->ver($id);
        if (!$usuario) {
            $_SESSION['flash_error'] = 'El usuario que intenta eliminar no existe.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Protección 2: No eliminar usuarios con compras/ventas registradas
        if ($this->usuariosModel->tieneVentas($id)) {
            $_SESSION['flash_error'] = 'No se puede eliminar el usuario "' . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) . '" porque tiene ventas asociadas en el sistema. Puedes desactivarlo cambiando su estado a "Inactivo".';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Ejecutar eliminación
        $resultado = $this->usuariosModel->eliminar($id);
        if ($resultado) {
            $_SESSION['flash_success'] = 'El usuario ha sido eliminado correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Ocurrió un error al intentar eliminar el usuario.';
        }

        header('Location: ' . BASE_URL . '/admin/usuarios');
        exit;
    }

    /**
     * Cambiar estado activo/inactivo rápidamente
     */
    public function cambiarEstado($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de usuario no válido.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $usuarioSesionId = $_SESSION['user_id'] ?? 0;
        if ($id === (int)$usuarioSesionId) {
            $_SESSION['flash_error'] = 'No puedes cambiar el estado de tu propia cuenta.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $usuario = $this->usuariosModel->ver($id);
        if (!$usuario) {
            $_SESSION['flash_error'] = 'El usuario no existe.';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $nuevoEstado = $usuario['activo'] == 1 ? 0 : 1;
        $this->usuariosModel->cambiarEstado($id, $nuevoEstado);

        $_SESSION['flash_success'] = 'Estado del usuario actualizado a ' . ($nuevoEstado == 1 ? 'Activo' : 'Inactivo') . '.';
        header('Location: ' . BASE_URL . '/admin/usuarios');
        exit;
    }
}
