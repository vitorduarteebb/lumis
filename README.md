# Lumis ERP — Fases 1 a 3

## Requisitos

- PHP 8.2 ou superior (`pdo_mysql`, `mbstring`, `json`, `openssl`)
- Composer 2.x
- MySQL 8.x (obrigatório a partir da Fase 2)

## Instalação

```bash
composer install
copy .env.example .env
```

Edite o `.env`: `APP_URL`, credenciais MySQL (`DB_*`) e, se desejar, `MAIL_*` (hoje o driver padrão `log` registra e-mails no Monolog).

## Banco de dados (Fase 2)

1. Crie o banco (exemplo):

```sql
CREATE DATABASE lumis_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Rode as migrations:

```bash
php database/migrate.php
```

3. Rode o seed (empresa, loja, perfil master, permissões e usuário administrador):

```bash
php database/seed.php
```

### Usuário inicial (seed)

| Campo  | Valor                 |
|--------|------------------------|
| Nome   | Administrador Master |
| E-mail | admin@lumiserp.com     |
| Senha  | 123456                 |

## Servidor embutido do PHP

```bash
php -S localhost:8080 -t public
```

Acesse `http://localhost:8080`.

## Apache

`DocumentRoot` na pasta `public`. Use `public/.htaccess`.

## Autenticação e permissões (Fase 2)

- Login com **e-mail e senha** no MySQL (`password_hash` / `password_verify`).
- Perfis (`roles`), permissões (`permissions`), vínculos `role_permissions`, `user_roles`.
- Multiempresa: `companies`, `stores`, `users.company_id` / `users.store_id`, pivôs `user_companies` e `user_stores`.
- Recuperação de senha: `/password/forgot` e `/password/reset` (token em `password_resets`; e-mail via `MailService` — hoje `MAIL_DRIVER=log`).
- Auditoria: `audit_logs` (login com sucesso, falha, bloqueio, logout, reset de senha).

### Testar login

1. Acesse `/login`.
2. Entre com `admin@lumiserp.com` / `123456` (após o seed).
3. Você será redirecionado a `/dashboard` (requer permissão `dashboard.view`).

### Testar permissões

- O seed associa o perfil **master** a **todas** as permissões; o middleware `PermissionMiddleware` também libera rotas se existir o papel `master` na sessão.
- Nas views, use `can('slug.da.permissao')` (definido em `app/helpers.php`), por exemplo `can('clients.view')`.

## Estrutura principal

- `public/` — front controller e assets
- `app/` — MVC, middlewares, helpers, services, repositories, views
- `config/` — `config()` (ex.: `app`, `database`, `mail`, `navigation`)
- `routes/web.php` — rotas
- `storage/logs` — Monolog
- `database/migrations` — SQL
- `database/seeders` — seed PHP

## Interface e design system (Fase 3)

- **Layout autenticado** (`app/Views/layouts/main.php`): shell com sidebar + área principal, Bootstrap Icons, `app.css` + `components.css`.
- **Sidebar** (`app/Views/components/sidebar.php`): menu completo do ERP (itens futuros desabilitados), submenus com collapse, estado ativo, recolhimento no desktop (persistido em `localStorage`), gaveta no mobile com overlay.
- **Header** (`app/Views/components/header.php`): título da página, busca global (placeholder), sininho (placeholder), menu do usuário com logout.
- **Breadcrumb** logo abaixo do header.
- **Flash** (`app/Views/components/flash.php`): mensagens `success`, `error`, `warning`, `info` via `Session::flash(...)`.
- **Navegação**: `config/navigation.php` — altere aqui para novos módulos/URLs.
- **Helpers**: `lumis_current_path()` e `lumis_nav_active()` em `app/helpers.php`.
- **Dashboard**: KPIs, gráficos (Chart.js), agenda mini, atividades, alertas, atalhos, empty state de exemplo.
- **Design system**: classes `lumis-*` em `public/assets/css/components.css` (cards, tabelas, formulários, toolbar, paginação, badges, alertas, timeline, calendário, modal).
- **Esqueletos** (copiar para módulos): `app/Views/components/shell-listing.php`, `shell-form.php`, `shell-modal.php`.
- **JS de layout**: `public/assets/js/app.js` (sidebar, overlay, atalho `/` foca a busca placeholder).

### Uso nas views internas

Passe para o controller (e para `$this->view(...)`):

- `pageTitle` — título exibido no header.
- `breadcrumbs` — lista `[['label' => '...', 'href' => '/path'|null], ...]`.
- `title` — continua sendo o `<title>` da aba (pode ser igual ao `pageTitle`).

Exemplo:

```php
return $this->view('meu/modulo', [
    'title' => 'Clientes',
    'pageTitle' => 'Clientes',
    'breadcrumbs' => [
        ['label' => 'Início', 'href' => '/dashboard'],
        ['label' => 'Cadastros', 'href' => null],
        ['label' => 'Clientes', 'href' => null],
    ],
]);
```

Flash na sessão:

```php
Session::flash('success', 'Salvo com sucesso.');
Session::flash('error', 'Falha na validação.');
```

## Próximas fases

- Fase 4: dados reais nos gráficos e widgets (consultas ao MySQL)
