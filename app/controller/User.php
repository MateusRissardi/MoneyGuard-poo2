<?php

require_once '../app/model/User.php';
require_once '../app/model/Group.php';

class UserController
{

    // Método para o CDU02 (Login)
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

    // Método para o CDU01 (Cadastro)
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
}
?>