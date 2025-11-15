<?php
session_start();

define('BASE_URL', '/GitHub/MoneyGuard-poo2/public/');

require_once '../app/core/Database.php';
require_once '../app/controller/User.php';
require_once '../app/controller/Group.php';
require_once '../app/controller/Expense.php';
require_once '../app/controller/Settlement.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

switch (true) {
    case ($url == 'register'):
        $controller = new UserController();
        $controller->register();
        break;

    case ($url == 'login'):
        $controller = new UserController();
        $controller->login();
        break;

    case ($url == 'logout'):
        $controller = new UserController();
        $controller->logout();
        break;

    case ($url == 'dashboard'):
        $controller = new GroupController();
        $controller->index();
        break;

    case ($url == 'groups'):
        require_once '../views/pages/groups_list.php';
        break;

    case ($url == 'group/create'):
        $controller = new GroupController();
        $controller->create();
        break;

    case (preg_match('/^group\/view\/(\d+)$/', $url, $matches)):
        $controller = new GroupController();
        $controller->view($matches[1]);
        break;

    case ($url == 'group/add_member'):
        $controller = new GroupController();
        $controller->addMember();
        break;

    case ($url == 'group/join_with_code'):
        $controller = new GroupController();
        $controller->joinWithCode();
        break;

    case ($url == 'expense/create'):
        $controller = new ExpenseController();
        $controller->create();
        break;

    case ($url == 'expense/delete'):
        $controller = new ExpenseController();
        $controller->delete();
        break;

    case (preg_match('/^expense\/edit\/(\d+)$/', $url, $matches)):
        $controller = new ExpenseController();
        $controller->edit($matches[1]); // Passa o ID da despesa
        break;

    case ($url == 'expense/update'):
        $controller = new ExpenseController();
        $controller->update();
        break;

    case (preg_match('/^expense\/get_details\/(\d+)$/', $url, $matches)):
        $controller = new ExpenseController();
        $controller->getDetails($matches[1]);
        break;

    case (preg_match('/^group\/report\/(\d+)$/', $url, $matches)):
        $controller = new GroupController();
        $controller->report($matches[1]);
        break;

    case ($url == 'settlement/delete'):
        $controller = new SettlementController();
        $controller->delete();
        break;

    case ($url == 'settlement/create'):
        $controller = new SettlementController();
        $controller->create();
        break;

    case ($url == 'settlement/create_all_my_debts'):
        $controller = new SettlementController();
        $controller->createAllMyDebts();
        break;

    case (preg_match('/^group\/generate_code\/(\d+)$/', $url, $matches)):
        $controller = new GroupController();
        $controller->generateInviteCode($matches[1]);
        break;

    case ($url == 'group/update'):
        $controller = new GroupController();
        $controller->update();
        break;

    case ($url == 'group/delete'):
        $controller = new GroupController();
        $controller->delete();
        break;

    case ($url == 'group/remove_member'):
        $controller = new GroupController();
        $controller->removeMember();
        break;

    case ($url == 'transaction'):
        $controller = new GroupController();
        $controller->transfers();
        break;

    case ($url == 'recent_activities'):
        $controller = new GroupController();
        $controller->activities();
        break;

    case ($url == ''):
        if (isset($_SESSION['user_id'])) {
            header("Location: groups");
        } else {
            header("Location: login");
        }
        exit;

    default:
        echo "Página não encontrada (404)";
        break;
}
?>