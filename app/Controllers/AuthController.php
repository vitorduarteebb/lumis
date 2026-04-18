<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Services\AuthService;
use App\Services\PasswordResetService;

final class AuthController extends Controller
{
    private AuthService $authService;

    private PasswordResetService $passwordResetService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->passwordResetService = new PasswordResetService();
    }

    public function showLogin(Request $request): string
    {
        return $this->view('auth/login', [
            'title' => 'Entrar',
            'csrfField' => Csrf::field(),
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'layouts/guest');
    }

    public function login(Request $request): never
    {
        $token = $request->input('_csrf_token');
        $token = is_string($token) ? $token : null;
        if (!Csrf::validate($token)) {
            Session::flash('error', 'Sessão expirada ou token inválido. Tente novamente.');
            Response::redirect('/login');
        }

        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            Session::flash('error', 'Informe e-mail e senha.');
            Response::redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Informe um e-mail válido.');
            Response::redirect('/login');
        }

        $error = $this->authService->login($email, $password, $request);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/login');
        }

        Session::flash('success', 'Bem-vindo ao Lumis ERP.');
        Response::redirect('/dashboard');
    }

    public function showForgotPassword(Request $request): string
    {
        return $this->view('auth/forgot-password', [
            'title' => 'Esqueci minha senha',
            'csrfField' => Csrf::field(),
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'layouts/guest');
    }

    public function forgotPassword(Request $request): never
    {
        $token = $request->input('_csrf_token');
        $token = is_string($token) ? $token : null;
        if (!Csrf::validate($token)) {
            Session::flash('error', 'Sessão expirada ou token inválido. Tente novamente.');
            Response::redirect('/password/forgot');
        }

        $email = trim((string) $request->input('email', ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Informe um e-mail válido.');
            Response::redirect('/password/forgot');
        }

        $this->passwordResetService->requestReset(
            $email,
            (string) ($request->server('REMOTE_ADDR') ?? ''),
            (string) ($request->server('HTTP_USER_AGENT') ?? '')
        );

        Session::flash(
            'success',
            'Se existir cadastro para este e-mail, enviaremos instruções para redefinir a senha.'
        );
        Response::redirect('/password/forgot');
    }

    public function showResetPassword(Request $request): string
    {
        $email = trim((string) $request->input('email', ''));
        $plainToken = trim((string) $request->input('token', ''));

        if ($email === '' || $plainToken === '') {
            Session::flash('error', 'Link inválido ou incompleto.');
            Response::redirect('/password/forgot');
        }

        return $this->view('auth/reset-password', [
            'title' => 'Redefinir senha',
            'csrfField' => Csrf::field(),
            'email' => $email,
            'token' => $plainToken,
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'layouts/guest');
    }

    public function resetPassword(Request $request): never
    {
        $token = $request->input('_csrf_token');
        $token = is_string($token) ? $token : null;
        if (!Csrf::validate($token)) {
            Session::flash('error', 'Sessão expirada ou token inválido. Tente novamente.');
            Response::redirect('/password/forgot');
        }

        $email = trim((string) $request->input('email', ''));
        $plainToken = trim((string) $request->input('token', ''));
        $password = (string) $request->input('password', '');
        $confirm = (string) $request->input('password_confirmation', '');

        if ($email === '' || $plainToken === '') {
            Session::flash('error', 'Dados inválidos.');
            Response::redirect('/password/forgot');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'A senha deve ter no mínimo 6 caracteres.');
            Response::redirect('/password/reset?' . http_build_query(['email' => $email, 'token' => $plainToken]));
        }

        if (!hash_equals($password, $confirm)) {
            Session::flash('error', 'As senhas não conferem.');
            Response::redirect('/password/reset?' . http_build_query(['email' => $email, 'token' => $plainToken]));
        }

        $error = $this->passwordResetService->resetPassword($email, $plainToken, $password);
        if ($error !== null) {
            Session::flash('error', $error);
            Response::redirect('/password/reset?' . http_build_query(['email' => $email, 'token' => $plainToken]));
        }

        Session::flash('success', 'Senha redefinida com sucesso. Faça login com a nova senha.');
        Response::redirect('/login');
    }

    public function logout(Request $request): never
    {
        $this->authService->logout($request);

        $sessionName = (string) env('SESSION_NAME', 'lumis_session');
        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        Session::flash('success', 'Sessão encerrada.');
        Response::redirect('/login');
    }
}
