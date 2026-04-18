<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\CompanyProfileRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanySubscriptionRepository;
use App\Repositories\DigitalCertificateRepository;
use App\Repositories\EmailNotificationRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\StoreRepository;

final class ConfiguracoesController extends Controller
{
    public function gerais(Request $request): string
    {
        $cid = $this->requireCompany();
        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $companyRepo = new CompanyRepository();
        $company = $companyRepo->findById($cid);
        $profile = $profileRepo->findByCompanyId($cid);

        return $this->view('configuracoes/gerais', [
            'title' => 'Configurações gerais',
            'pageTitle' => 'Gerais',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Gerais', 'href' => null],
            ],
            'company' => $company ?? [],
            'profile' => $profile,
        ]);
    }

    public function geraisSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/gerais');
        }
        $appTitle = trim((string) $request->input('app_title', ''));
        $displayName = trim((string) $request->input('display_name', ''));
        $timezone = trim((string) $request->input('timezone', 'America/Sao_Paulo'));
        $locale = trim((string) $request->input('locale', 'pt_BR'));
        $currency = trim((string) $request->input('default_currency', 'BRL'));
        $pageSize = max(5, min(100, (int) $request->input('default_page_size', 15)));
        $companyName = trim((string) $request->input('company_name', ''));

        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $profileRepo->updateProfile($cid, [
            'app_title' => $appTitle === '' ? null : $appTitle,
            'display_name' => $displayName === '' ? null : $displayName,
            'timezone' => $timezone === '' ? 'America/Sao_Paulo' : $timezone,
            'locale' => $locale === '' ? 'pt_BR' : $locale,
            'default_currency' => $currency === '' ? 'BRL' : strtoupper(substr($currency, 0, 10)),
            'default_page_size' => $pageSize,
        ]);

        if ($companyName !== '') {
            $companyRepo = new CompanyRepository();
            $row = $companyRepo->findById($cid);
            if ($row !== null) {
                $slug = lumis_slugify($companyName);
                $companyRepo->updateBasics($cid, $companyName, $slug, (int) ($row['status'] ?? 1));
            }
        }

        Session::flash('success', 'Configurações gerais salvas.');
        redirect('/configuracoes/gerais');
    }

    public function meuPlano(Request $request): string
    {
        $cid = $this->requireCompany();
        $subRepo = new CompanySubscriptionRepository();
        $row = $subRepo->findByCompanyId($cid);

        return $this->view('configuracoes/meu_plano', [
            'title' => 'Meu plano',
            'pageTitle' => 'Meu plano',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Meu plano', 'href' => null],
            ],
            'subscription' => $row,
        ]);
    }

    public function meuPlanoSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/meu-plano');
        }
        $subRepo = new CompanySubscriptionRepository();
        $renewsRaw = trim((string) $request->input('renews_at', ''));
        $renewsAt = $renewsRaw === '' ? null : $renewsRaw;
        $subRepo->updateSubscription($cid, [
            'plan_name' => trim((string) $request->input('plan_name', 'Standard')),
            'status' => trim((string) $request->input('status', 'active')),
            'max_users' => max(1, (int) $request->input('max_users', 50)),
            'renews_at' => $renewsAt,
            'notes' => trim((string) $request->input('notes', '')) === '' ? null : trim((string) $request->input('notes', '')),
        ]);
        Session::flash('success', 'Plano atualizado.');
        redirect('/configuracoes/meu-plano');
    }

    public function dadosEmpresa(Request $request): string
    {
        $cid = $this->requireCompany();
        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $profile = $profileRepo->findByCompanyId($cid);

        return $this->view('configuracoes/dados_empresa', [
            'title' => 'Dados da empresa',
            'pageTitle' => 'Dados da empresa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Dados da empresa', 'href' => null],
            ],
            'profile' => $profile,
        ]);
    }

    public function dadosEmpresaSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/dados-empresa');
        }
        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $profileRepo->updateProfile($cid, [
            'trade_name' => trim((string) $request->input('trade_name', '')) === '' ? null : trim((string) $request->input('trade_name', '')),
            'website' => trim((string) $request->input('website', '')) === '' ? null : trim((string) $request->input('website', '')),
            'legal_name' => trim((string) $request->input('legal_name', '')) === '' ? null : trim((string) $request->input('legal_name', '')),
            'document_cnpj' => trim((string) $request->input('document_cnpj', '')) === '' ? null : trim((string) $request->input('document_cnpj', '')),
            'state_registration' => trim((string) $request->input('state_registration', '')) === '' ? null : trim((string) $request->input('state_registration', '')),
            'municipal_registration' => trim((string) $request->input('municipal_registration', '')) === '' ? null : trim((string) $request->input('municipal_registration', '')),
            'email' => trim((string) $request->input('email', '')) === '' ? null : trim((string) $request->input('email', '')),
            'phone' => trim((string) $request->input('phone', '')) === '' ? null : trim((string) $request->input('phone', '')),
            'mobile' => trim((string) $request->input('mobile', '')) === '' ? null : trim((string) $request->input('mobile', '')),
            'cep' => trim((string) $request->input('cep', '')) === '' ? null : trim((string) $request->input('cep', '')),
            'street' => trim((string) $request->input('street', '')) === '' ? null : trim((string) $request->input('street', '')),
            'address_number' => trim((string) $request->input('address_number', '')) === '' ? null : trim((string) $request->input('address_number', '')),
            'complement' => trim((string) $request->input('complement', '')) === '' ? null : trim((string) $request->input('complement', '')),
            'district' => trim((string) $request->input('district', '')) === '' ? null : trim((string) $request->input('district', '')),
            'city' => trim((string) $request->input('city', '')) === '' ? null : trim((string) $request->input('city', '')),
            'state' => trim((string) $request->input('state', '')) === '' ? null : strtoupper(substr(trim((string) $request->input('state', '')), 0, 2)),
            'notes' => trim((string) $request->input('notes', '')) === '' ? null : trim((string) $request->input('notes', '')),
        ]);
        Session::flash('success', 'Dados da empresa salvos.');
        redirect('/configuracoes/dados-empresa');
    }

    public function marcaEmpresa(Request $request): string
    {
        $cid = $this->requireCompany();
        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $profile = $profileRepo->findByCompanyId($cid);

        return $this->view('configuracoes/marca_empresa', [
            'title' => 'Marca da empresa',
            'pageTitle' => 'Marca da empresa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Marca da empresa', 'href' => null],
            ],
            'profile' => $profile,
        ]);
    }

    public function marcaEmpresaSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/marca-empresa');
        }
        $primary = trim((string) $request->input('primary_color', ''));
        $accent = trim((string) $request->input('accent_color', ''));

        $logoPath = null;
        if (isset($_FILES['logo']) && is_array($_FILES['logo']) && (int) ($_FILES['logo']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tmp = (string) ($_FILES['logo']['tmp_name'] ?? '');
            $mime = $tmp !== '' && is_file($tmp) ? (string) (mime_content_type($tmp) ?: '') : '';
            if (!in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
                Session::flash('error', 'Logo deve ser PNG, JPEG ou WebP.');
                redirect('/configuracoes/marca-empresa');
            }
            $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
            $dir = base_path('public/uploads/logos/' . $cid);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                Session::flash('error', 'Não foi possível criar pasta de uploads.');
                redirect('/configuracoes/marca-empresa');
            }
            $dest = $dir . '/logo.' . $ext;
            if (!move_uploaded_file($tmp, $dest)) {
                Session::flash('error', 'Falha ao salvar o arquivo.');
                redirect('/configuracoes/marca-empresa');
            }
            $logoPath = '/uploads/logos/' . $cid . '/logo.' . $ext;
        }

        $profileRepo = new CompanyProfileRepository();
        $profileRepo->ensureRow($cid);
        $displayName = trim((string) $request->input('display_name', ''));
        $faviconPath = null;
        if (isset($_FILES['favicon']) && is_array($_FILES['favicon']) && (int) ($_FILES['favicon']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tmp = (string) ($_FILES['favicon']['tmp_name'] ?? '');
            $mime = $tmp !== '' && is_file($tmp) ? (string) (mime_content_type($tmp) ?: '') : '';
            if (!in_array($mime, ['image/png', 'image/jpeg', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'], true)) {
                Session::flash('error', 'Favicon deve ser PNG, JPEG, WebP ou ICO.');
                redirect('/configuracoes/marca-empresa');
            }
            $ext = str_contains($mime, 'icon') ? 'ico' : ($mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg'));
            $dir = base_path('public/uploads/favicons/' . $cid);
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                Session::flash('error', 'Não foi possível criar pasta de uploads.');
                redirect('/configuracoes/marca-empresa');
            }
            $dest = $dir . '/favicon.' . $ext;
            if (!move_uploaded_file($tmp, $dest)) {
                Session::flash('error', 'Falha ao salvar o favicon.');
                redirect('/configuracoes/marca-empresa');
            }
            $faviconPath = '/uploads/favicons/' . $cid . '/favicon.' . $ext;
        }

        $data = [
            'display_name' => $displayName === '' ? null : $displayName,
            'primary_color' => $primary === '' ? null : $primary,
            'accent_color' => $accent === '' ? null : $accent,
        ];
        if ($logoPath !== null) {
            $data['logo_path'] = $logoPath;
        }
        if ($faviconPath !== null) {
            $data['favicon_path'] = $faviconPath;
        }
        $profileRepo->updateProfile($cid, $data);

        Session::flash('success', 'Marca atualizada.');
        redirect('/configuracoes/marca-empresa');
    }

    public function empresasLojas(Request $request): string
    {
        $cid = $this->requireCompany();
        $companyRepo = new CompanyRepository();
        $storeRepo = new StoreRepository();
        $company = $companyRepo->findById($cid);
        $stores = $storeRepo->allForCompany($cid);

        return $this->view('configuracoes/empresas_lojas', [
            'title' => 'Empresas / Lojas',
            'pageTitle' => 'Empresas / Lojas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Empresas / Lojas', 'href' => null],
            ],
            'company' => $company ?? [],
            'stores' => $stores,
        ]);
    }

    public function empresasLojasStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/empresas-lojas');
        }
        $action = (string) $request->input('_action', '');
        $storeRepo = new StoreRepository();
        $redir = '/configuracoes/empresas-lojas';

        if ($action === 'create_store') {
            $name = trim((string) $request->input('store_name', ''));
            if ($name === '') {
                Session::flash('error', 'Informe o nome da loja.');
                redirect($redir);
            }
            $slug = trim((string) $request->input('store_slug', ''));
            if ($slug === '') {
                $slug = lumis_slugify($name);
            }
            if ($storeRepo->slugExists($cid, $slug, null)) {
                Session::flash('error', 'Já existe uma loja com este slug.');
                redirect($redir);
            }
            $st = (int) $request->input('store_status', 1) === 0 ? 0 : 1;
            $storeRepo->insert($cid, $name, $slug, $st);
            Session::flash('success', 'Loja cadastrada.');
            redirect($redir);
        }

        if ($action === 'update_store') {
            $id = (int) $request->input('store_id', 0);
            $row = $id > 0 ? $storeRepo->findByIdForCompany($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Loja não encontrada.');
                redirect($redir);
            }
            $name = trim((string) $request->input('name', ''));
            $slug = trim((string) $request->input('slug', ''));
            if ($name === '' || $slug === '') {
                Session::flash('error', 'Nome e slug são obrigatórios.');
                redirect($redir);
            }
            if ($storeRepo->slugExists($cid, $slug, $id)) {
                Session::flash('error', 'Slug já em uso.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $storeRepo->update($id, $cid, $name, $slug, $st);
            Session::flash('success', 'Loja atualizada.');
            redirect($redir);
        }

        if ($action === 'delete_store') {
            $id = (int) $request->input('store_id', 0);
            if ($id < 1 || $storeRepo->findByIdForCompany($id, $cid) === null) {
                Session::flash('error', 'Loja não encontrada.');
                redirect($redir);
            }
            $storeRepo->setStatus($id, $cid, 0);
            Session::flash('success', 'Loja inativada.');
            redirect($redir);
        }

        Session::flash('error', 'Ação inválida.');
        redirect($redir);
    }

    public function certificadoDigital(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new DigitalCertificateRepository();
        $rows = $repo->listByCompany($cid);

        return $this->view('configuracoes/certificado_digital', [
            'title' => 'Certificado digital',
            'pageTitle' => 'Certificado digital',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Certificado digital', 'href' => null],
            ],
            'certificates' => $rows,
        ]);
    }

    public function certificadoDigitalSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/certificado-digital');
        }
        $label = trim((string) $request->input('label', ''));
        if ($label === '') {
            Session::flash('error', 'Informe um rótulo para o certificado.');
            redirect('/configuracoes/certificado-digital');
        }
        $expiresRaw = trim((string) $request->input('expires_at', ''));
        $expiresAt = $expiresRaw === '' ? null : $expiresRaw;
        $filePath = null;
        if (isset($_FILES['cert_file']) && is_array($_FILES['cert_file']) && (int) ($_FILES['cert_file']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tmp = (string) ($_FILES['cert_file']['tmp_name'] ?? '');
            $dir = base_path('storage/uploads/certificates/' . $cid);
            if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
                Session::flash('error', 'Não foi possível criar pasta de armazenamento.');
                redirect('/configuracoes/certificado-digital');
            }
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string) ($_FILES['cert_file']['name'] ?? 'cert')) ?: 'cert.bin';
            $dest = $dir . '/' . time() . '_' . $safeName;
            if ($tmp !== '' && move_uploaded_file($tmp, $dest)) {
                $filePath = 'storage/uploads/certificates/' . $cid . '/' . basename($dest);
            }
        }
        $repo = new DigitalCertificateRepository();
        $certType = trim((string) $request->input('cert_type', 'A1'));
        $notes = trim((string) $request->input('notes', ''));
        $pwPlain = (string) $request->input('cert_password', '');
        $pwEnc = $pwPlain !== '' ? lumis_encrypt_secret($pwPlain) : null;
        $repo->insert(
            $cid,
            $label,
            $expiresAt,
            $filePath,
            $filePath !== null ? 'stored' : 'pending',
            $certType !== '' ? $certType : 'A1',
            $notes === '' ? null : $notes,
            $pwEnc
        );
        Session::flash('success', 'Registro de certificado criado.');
        redirect('/configuracoes/certificado-digital');
    }

    public function certificadoDigitalDelete(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/certificado-digital');
        }
        $id = (int) $request->input('id', 0);
        $repo = new DigitalCertificateRepository();
        $row = $id > 0 ? $repo->findByIdForCompany($id, $cid) : null;
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/configuracoes/certificado-digital');
        }
        $fp = (string) ($row['file_path'] ?? '');
        if ($fp !== '') {
            $abs = base_path($fp);
            if (is_file($abs)) {
                @unlink($abs);
            }
        }
        $repo->delete($id, $cid);
        Session::flash('success', 'Registro removido.');
        redirect('/configuracoes/certificado-digital');
    }

    public function modelosEmail(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new EmailTemplateRepository();
        $rows = $repo->listByCompany($cid);

        return $this->view('configuracoes/modelos_email', [
            'title' => 'Modelos de e-mails',
            'pageTitle' => 'Modelos de e-mails',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Modelos de e-mails', 'href' => null],
            ],
            'templates' => $rows,
        ]);
    }

    public function modelosEmailNovo(Request $request): string
    {
        $cid = $this->requireCompany();

        return $this->view('configuracoes/modelos_email_form', [
            'title' => 'Novo modelo de e-mail',
            'pageTitle' => 'Novo modelo',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Modelos de e-mails', 'href' => '/configuracoes/modelos-email'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'template' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function modelosEmailStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/modelos-email/novo');
        }
        $repo = new EmailTemplateRepository();
        $data = $this->extractTemplatePayload($request);
        $errors = $this->validateTemplate($data, $repo, $cid, null);
        if ($errors !== []) {
            return $this->view('configuracoes/modelos_email_form', [
                'title' => 'Novo modelo de e-mail',
                'pageTitle' => 'Novo modelo',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Configurações', 'href' => null],
                    ['label' => 'Modelos de e-mails', 'href' => '/configuracoes/modelos-email'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'template' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $id = $repo->insert($cid, $data);
        Session::flash('success', 'Modelo criado.');
        redirect('/configuracoes/modelos-email/' . $id . '/editar');
    }

    public function modelosEmailEditar(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/modelos-email');
        }
        $repo = new EmailTemplateRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Modelo não encontrado.');
            redirect('/configuracoes/modelos-email');
        }

        return $this->view('configuracoes/modelos_email_form', [
            'title' => 'Editar modelo',
            'pageTitle' => 'Editar modelo',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Modelos de e-mails', 'href' => '/configuracoes/modelos-email'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'template' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function modelosEmailUpdate(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/modelos-email');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/modelos-email');
        }
        $repo = new EmailTemplateRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Modelo não encontrado.');
            redirect('/configuracoes/modelos-email');
        }
        $data = $this->extractTemplatePayload($request);
        $errors = $this->validateTemplate($data, $repo, $cid, $id);
        if ($errors !== []) {
            return $this->view('configuracoes/modelos_email_form', [
                'title' => 'Editar modelo',
                'pageTitle' => 'Editar modelo',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Configurações', 'href' => null],
                    ['label' => 'Modelos de e-mails', 'href' => '/configuracoes/modelos-email'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'template' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $repo->update($id, $cid, $data);
        Session::flash('success', 'Modelo atualizado.');
        redirect('/configuracoes/modelos-email/' . $id . '/editar');
    }

    public function modelosEmailDestroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/modelos-email');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/modelos-email');
        }
        $repo = new EmailTemplateRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Modelo não encontrado.');
            redirect('/configuracoes/modelos-email');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Modelo inativado.');
        redirect('/configuracoes/modelos-email');
    }

    public function avisosEmail(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new EmailNotificationRepository();
        $map = $repo->mapForCompany($cid);
        $tplMap = $repo->templateMapForCompany($cid);
        $templates = (new EmailTemplateRepository())->listByCompany($cid);

        return $this->view('configuracoes/avisos_email', [
            'title' => 'Avisos por e-mail',
            'pageTitle' => 'Avisos por e-mail',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Avisos por e-mail', 'href' => null],
            ],
            'eventLabels' => EmailNotificationRepository::EVENT_LABELS,
            'enabledMap' => $map,
            'templateMap' => $tplMap,
            'emailTemplates' => $templates,
        ]);
    }

    public function avisosEmailSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/avisos-email');
        }
        $repo = new EmailNotificationRepository();
        foreach (array_keys(EmailNotificationRepository::EVENT_LABELS) as $key) {
            $en = (int) $request->input('event_' . $key, 0) === 1 ? 1 : 0;
            $tplRaw = (int) $request->input('template_' . $key, 0);
            $tplId = $tplRaw > 0 ? $tplRaw : null;
            $repo->upsert($cid, $key, $en, $tplId);
        }
        Session::flash('success', 'Preferências de notificação salvas.');
        redirect('/configuracoes/avisos-email');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractTemplatePayload(Request $request): array
    {
        return [
            'slug' => trim((string) $request->input('slug', '')),
            'name' => trim((string) $request->input('name', '')),
            'subject' => trim((string) $request->input('subject', '')),
            'body_html' => (string) $request->input('body_html', ''),
            'status' => (int) $request->input('status', 1) === 0 ? 0 : 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateTemplate(array $data, EmailTemplateRepository $repo, int $companyId, ?int $exceptId): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($data['slug'] === '') {
            $errors['slug'] = 'Informe o identificador (slug).';
        } elseif ($repo->slugExists($companyId, $data['slug'], $exceptId)) {
            $errors['slug'] = 'Slug já em uso.';
        }
        if ($data['subject'] === '') {
            $errors['subject'] = 'Informe o assunto.';
        }
        if (trim($data['body_html']) === '') {
            $errors['body_html'] = 'Informe o corpo HTML.';
        }

        return $errors;
    }

    private function requireCompany(): int
    {
        $cid = current_company_id();
        if ($cid === null) {
            Session::flash('error', 'Empresa não definida.');
            redirect('/dashboard');
        }

        return $cid;
    }
}
