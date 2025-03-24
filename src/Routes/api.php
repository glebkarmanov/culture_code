<?php
use Bramus\Router\Router;
use Src\Controllers\Api\AuthenticationController;
use Src\Controllers\Api\CompaniesController;
use Src\Controllers\Api\SurveysController;

$router = new Router();

$router->post('/api/register', function () {
    $controller = new AuthenticationController();
    $controller->register();
});

$router->post('/api/login', function () {
    $controller = new AuthenticationController();
    $controller->signIn();
});

$router->get('/api/companies', function () {
    $controller = new CompaniesController();
    $controller->getInfo();
});

$router->put('/api/companies', function () {
    $controller = new CompaniesController();
    $controller->changeNameAndEmail();
});

$router->get('/api/companies/members', function () {
    $controller = new CompaniesController();
    $controller->getMembers();
});

$router->post('/api/companies/add_member', function () {
    $controller = new CompaniesController();
    $controller->addMember();
});

$router->get('/api/companies/member', function () {
    $controller = new CompaniesController();
    $controller->getMember();
});

$router->put('/api/companies/update_member', function () {
    $controller = new CompaniesController();
    $controller->updateMember();
});

$router->delete('/api/companies/delete_member', function () {
    $controller = new CompaniesController();
    $controller->deleteMember();
});

$router->delete('/api/companies/teams', function () {
    $controller = new CompaniesController();
    $controller->deleteMember();
});

$router->post('/api/forgot-password', function () {
    $controller = new AuthenticationController();
    $controller->forgotPassword();
});

$router->get('/api/survey', function () {
    $controller = new SurveysController();
    $controller->getSurvey();
});

$router->run();
