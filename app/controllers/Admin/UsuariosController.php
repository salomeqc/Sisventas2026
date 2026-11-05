<?php
namespace App\Controllers\Admin;

include_once __DIR__ . '/../../models/Admin/Roles.php';
include_once __DIR__ . '/../../models/Admin/Usuarios.php';
include_once __DIR__ . '/../../models/Admin/Ventas.php';

use Core\Controller;
use App\Models\Admin\Roles;
use App\Models\Admin\Usuarios;
use App\Models\Admin\Ventas;

class UsuariosController extends Controller
{
    protected $rolesModel; // atributo de la clase controller para manejar el modelo Roles
    protected $usuariosModel; // atributo de la clase controller para manejar el modelo Usuarios
    protected $ventasModel; // atributo de la clase controller para manejar el modelo Ventas

    public function __construct(){
        parent::__construct();
        // Inicializar el modelo Roles
        $this->rolesModel = new Roles();
        // Inicializar el modelo Usuarios
        $this->usuariosModel = new Usuarios();
        // Inicializar el modelo Ventas
        $this->ventasModel = new Ventas();
    }

    public function index()
    {
        $criterio = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if(!empty($criterio)){
            $usuarios = $this->usuariosModel->buscar($criterio);
        } else {
            $usuarios = $this->usuariosModel->buscar();
        }

        $this->view('admin/usuarios/index', [
            'isAdmin'   => true,
            'usuarios'  => $usuarios,
            'module'    => 'admin',
            'pageTitle' => 'Usuarios',
            'criterio'  => $criterio
        ]);
        exit;
    }

    public function buscar()
    {
        $criterio = $_POST['buscar'] ?? $_GET['buscar'] ?? '';
        $usuarios = $this->usuariosModel->buscar($criterio);

        $this->view('admin/usuarios/index', [
            'isAdmin'   => true,
            'usuarios'  => $usuarios,
            'module'    => 'admin',
            'pageTitle' => 'Usuarios',
            'criterio'  => $criterio
        ]);
        exit;
    }

    public function nuevo(){
        $roles = $this->rolesModel->roles_select();
        $this->view('admin/usuarios/nuevo', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Nuevo Usuario',
            'roles'     => $roles
        ]);
        exit;
    }

    public function guardar(){
        $data = $_POST; // la variable $data contiene los datos enviados desde el formulario
        // Validar los datos recibidos
        if(!empty($data['rol']) && !empty($data['nombre']) && !empty($data['apellidos']) && !empty($data['email']) && !empty($data['password'])){
            // Verificar si el correo ya existe
            $existe = $this->usuariosModel->obtenerPorEmail($data['email']);
            if($existe){
                $_SESSION['flash_error'] = 'El correo electrónico ya se encuentra registrado';
                header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
                exit;
            }

            // Validar confirmacion de contraseña si existe
            if(!empty($data['confirm_password']) && $data['password'] !== $data['confirm_password']){
                $_SESSION['flash_error'] = 'Las contraseñas no coinciden';
                header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
                exit;
            }

            // GUARDAR EL USUARIO EN LA BASE DE DATOS
            // LLAMAR AL MODELO DE USUARIOS PARA GUARDAR EL USUARIO
            $resultado = $this->usuariosModel->registrar($data);

            if($resultado){
                // Notificar al usuario que el usuario se registró correctamente
                $_SESSION['flash_success'] = 'Usuario registrado correctamente';
                // Redirección a la lista de usuarios
                header('Location: ' . BASE_URL . '/admin/usuarios');
            } else {
                // Notificar al usuario que hubo un error al registrar el usuario
                $_SESSION['flash_error'] = 'Error al registrar el usuario';
                // Redirección al formulario de nuevo usuario
                header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
            }
        }else{
            $_SESSION['flash_error'] = 'Error: Campos obligatorios vacíos';
            header('Location: ' . BASE_URL . '/admin/usuarios/nuevo');
        }
        exit;
    }

    // metodo editar
    public function editar($id){
        // Estos roles se utilizan para llenar el selector del formulario
        $roles = $this->rolesModel->roles_select();
        // buscar un usuario por id
        $usuario = $this->usuariosModel->ver((int)$id);
        if(!$usuario){
            $_SESSION['flash_error'] = 'Usuario no encontrado';
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        // Envía a la vista los datos necesarios para renderizar el formulario
        $this->view('admin/usuarios/editar', [
            'isAdmin'   => true,
            'module'    => 'admin',
            'pageTitle' => 'Editar Usuario',
            'roles'     => $roles,
            'usuario'   => $usuario
        ]);
        exit;
    }

    // metodo actualizar
    public function actualizar(){
        $data = $_POST;
        if(!empty($data['id']) && !empty($data['rol']) && !empty($data['nombre']) && !empty($data['apellidos']) && !empty($data['email'])){
            // Verificar si el correo ya existe en otro usuario
            $existe = $this->usuariosModel->obtenerPorEmail($data['email'], $data['id']);
            if($existe){
                $_SESSION['flash_error'] = 'El correo electrónico ya pertenece a otro usuario';
                header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . $data['id']);
                exit;
            }

            // Validar contraseñas si se ingresó nueva
            if(!empty($data['password'])){
                if(!empty($data['confirm_password']) && $data['password'] !== $data['confirm_password']){
                    $_SESSION['flash_error'] = 'Las contraseñas no coinciden';
                    header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . $data['id']);
                    exit;
                }
            }

            // actualizar el usuario
            $respuesta = $this->usuariosModel->actualizar($data);
            if($respuesta){
                // notificar al usuario que se guardo correctamente
                $_SESSION['flash_success'] = "Usuario actualizado correctamente";
                // redireccion a la lista de usuarios
                header('Location: ' . BASE_URL . '/admin/usuarios');
            }else{
                // notificar al usuario que hubo un error al guardar el usuario
                $_SESSION['flash_error'] = "Error al actualizar el usuario";
                header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . $data['id']);
            }
        }else{
            $_SESSION['flash_error'] = "Error: Campos obligatorios vacíos";
            header('Location: ' . BASE_URL . '/admin/usuarios/editar/' . ($data['id'] ?? ''));
        }
        exit;
    }

    // metodo ver / consultar
    public function ver($id){
        $usuario = $this->usuariosModel->ver((int)$id);
        if(!$usuario){
            $_SESSION['flash_error'] = 'Usuario no encontrado';
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

    public function consultar($id){
        $this->ver($id);
    }

    // metodo eliminar
    public function eliminar($id){
        // Evitar que el administrador se autoelimine
        if ($id == ($_SESSION['user_id'] ?? null)) {
            $_SESSION['flash_error'] = "No puedes eliminar tu propio usuario en sesión activa";
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit;
        }

        $ventas_usuario = $this->usuariosModel->ver_ventas_usuario((int)$id);
        if($ventas_usuario){
            $_SESSION['flash_error'] = "No se puede eliminar el usuario porque tiene ventas asociadas";
            header('Location: ' . BASE_URL . '/admin/usuarios');
        }else{
            // Llamar al modelo de usuarios para eliminar el usuario
            $eliminar = $this->usuariosModel->eliminar((int)$id);
            if($eliminar){
                $_SESSION['flash_success'] = "Usuario eliminado correctamente";
                header('Location: ' . BASE_URL . '/admin/usuarios');
            }else{
                $_SESSION['flash_error'] = "Error al eliminar el usuario";
                header('Location: ' . BASE_URL . '/admin/usuarios');
            }
        }
        exit;
    }
}