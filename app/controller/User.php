<?php

require_once '../app/model/User.php';
require_once '../app/model/Group.php';

class UserController
{

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $email = $_POST['email'];
            $senha = $_POST['senha'];

            if (empty($email) || empty($senha)) {
                $error = "E-mail e senha são obrigatórios.";
                require_once '../views/pages/login.php';
                return;
            }

            $db = Database::getInstance()->getConnection();
            $userModel = new User($db);

            $result = $userModel->login($email, $senha);

            if (is_array($result)) {

                $_SESSION['user_id'] = $result['id_usuario'];
                $_SESSION['user_name'] = $result['nome'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['ultimo_grupo_acessado_id'] = $result['ultimo_grupo_acessado_id'];

                header("Location: dashboard");
                exit;
            } else {
                $error = $result;
                require_once '../views/pages/login.php';
            }

        } else {
            require_once '../views/pages/login.php';
        }
    }

    public function logout()
    {
        $_SESSION = array();

        session_destroy();

        header("Location: login");
        exit;
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            $data_nascimento = $_POST['data_nascimento'];
            if (empty($nome) || empty($email) || empty($senha) || empty($data_nascimento)) {
                $error = "Todos os campos são obrigatórios.";
                require_once '../views/pages/register.php';
                return;
            }

            $hoje = date('Y-m-d');
            if ($data_nascimento >= $hoje) {
                $error = "A data de nascimento deve ser no passado. (RN-ORG14)";
                require_once '../views/pages/register.php';
                return;
            }

            $db = Database::getInstance()->getConnection();
            $userModel = new User($db);

            $result = $userModel->register($nome, $email, $senha, $data_nascimento);

            if (is_int($result)) {
                $new_user_id = $result;
                $redirect_message = "status=success";

                if (!empty($codigo_convite)) {
                    $groupModel = new Group($db);
                    $join_result = $groupModel->joinWithCode($codigo_convite, $new_user_id);

                    if ($join_result === true) {
                        $redirect_message = "status=success_joined";
                    } else {
                        $redirect_message = "status=success_join_failed&join_error=" . urlencode($join_result);
                    }
                }

                header("Location: login?" . $redirect_message);
                exit;

            } else {
                $error = $result;
                require_once '../views/pages/register.php';
            }

        } else {
            require_once '../views/pages/register.php';
        }
    }

    public function settings()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login?error=auth");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $userModel = new User($db);
        $user = $userModel->getUserById($_SESSION['user_id']);

        $groupModel = new Group($db);
        $sidebar_grupos = $groupModel->getGroupsByUser($_SESSION['user_id']);

        require_once '../views/pages/group_settings.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "dashboard");
            exit;
        }

        $id_usuario = $_SESSION['user_id'];
        $tipo = $_POST['type'];
        $data = [];

        if ($tipo === 'nome') {
            $data['nome'] = $_POST['nome'];
            $_SESSION['user_name'] = $data['nome'];
        } elseif ($tipo === 'email') {
            $data['email'] = $_POST['email'];
            $_SESSION['user_email'] = $data['email'];
        } elseif ($tipo === 'senha') {
            if ($_POST['senha'] === $_POST['confirma_senha']) {
                $data['senha'] = $_POST['senha'];
            } else {
                header("Location: " . BASE_URL . "settings?error=" . urlencode("As senhas não coincidem."));
                exit;
            }
        }

        $userModel = new User(Database::getInstance()->getConnection());
        if ($userModel->update($id_usuario, $data)) {
            header("Location: " . BASE_URL . "settings?status=updated");
        } else {
            header("Location: " . BASE_URL . "settings?error=update_failed");
        }
        exit;
    }

    public function deleteAccount()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "dashboard");
            exit;
        }

        $userModel = new User(Database::getInstance()->getConnection());
        if ($userModel->delete($_SESSION['user_id'])) {
            session_destroy();
            header("Location: " . BASE_URL . "login?status=account_deleted");
        } else {
            header("Location: " . BASE_URL . "settings?error=delete_failed");
        }
        exit;
    }
}
?>