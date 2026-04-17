<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\PriceListRepository;
use App\Repositories\ProductCatalogRepository;
use App\Repositories\ProductRepository;

final class ProdutosController extends Controller
{
    private const PER_PAGE = 15;

    private const CATALOG_PER_PAGE = 20;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $catRaw = $request->input('category_id', '');
        $categoryId = $catRaw === '' || $catRaw === null ? null : (int) $catRaw;
        if ($categoryId !== null && $categoryId < 1) {
            $categoryId = null;
        }
        $page = max(1, (int) $request->input('page', 1));

        $repo = new ProductRepository();
        $result = $repo->paginate($cid, $q, $status, $categoryId, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;
        $categories = $repo->categoriesForCompany($cid);

        return $this->view('produtos/index', [
            'title' => 'Produtos',
            'pageTitle' => 'Produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Gerenciar produtos', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'categoryFilter' => $categoryId,
            'categories' => $categories,
            'basePath' => '/produtos',
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status,
                'category_id' => $categoryId,
            ]),
        ]);
    }

    public function create(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new ProductRepository();

        return $this->view('produtos/form', [
            'title' => 'Novo produto',
            'pageTitle' => 'Novo produto',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'product' => null,
            'categories' => $repo->categoriesForCompany($cid),
            'brands' => $repo->brandsForCompany($cid),
            'units' => $repo->unitsForCompany($cid),
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos/novo');
        }

        $repo = new ProductRepository();
        $data = $this->extractProductPayload($request);
        $errors = $this->validateProduct($data, $repo, $cid, false, null);
        if ($errors !== []) {
            return $this->view('produtos/form', [
                'title' => 'Novo produto',
                'pageTitle' => 'Novo produto',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Produtos', 'href' => null],
                    ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'product' => null,
                'categories' => $repo->categoriesForCompany($cid),
                'brands' => $repo->brandsForCompany($cid),
                'units' => $repo->unitsForCompany($cid),
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $id = $repo->insert($cid, $this->normalizeProductForDb($data), auth_id());
        Session::flash('success', 'Produto cadastrado com sucesso.');
        redirect('/produtos/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/produtos');
        }
        $repo = new ProductRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        return $this->view('produtos/show', [
            'title' => (string) $row['name'],
            'pageTitle' => (string) $row['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'product' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/produtos');
        }
        $repo = new ProductRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        return $this->view('produtos/form', [
            'title' => 'Editar produto',
            'pageTitle' => 'Editar produto',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'product' => $row,
            'categories' => $repo->categoriesForCompany($cid),
            'brands' => $repo->brandsForCompany($cid),
            'units' => $repo->unitsForCompany($cid),
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/produtos');
        }
        $repo = new ProductRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }

        $data = $this->extractProductPayload($request);
        $errors = $this->validateProduct($data, $repo, $cid, true, $id);
        if ($errors !== []) {
            return $this->view('produtos/form', [
                'title' => 'Editar produto',
                'pageTitle' => 'Editar produto',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Produtos', 'href' => null],
                    ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'product' => $row,
                'categories' => $repo->categoriesForCompany($cid),
                'brands' => $repo->brandsForCompany($cid),
                'units' => $repo->unitsForCompany($cid),
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo->update($id, $cid, $this->normalizeProductForDb($data), auth_id());
        Session::flash('success', 'Produto atualizado com sucesso.');
        redirect('/produtos/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/produtos');
        }
        $repo = new ProductRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Produto não encontrado.');
            redirect('/produtos');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Produto removido (exclusão lógica).');
        redirect('/produtos');
    }

    public function valoresVenda(Request $request): string
    {
        $cid = $this->requireCompany();
        $pl = new PriceListRepository();
        $defaultId = $pl->ensureDefault($cid);
        $listId = (int) $request->input('list_id', $defaultId);
        if ($listId < 1) {
            $listId = $defaultId;
        }
        $lists = $pl->listByCompany($cid);
        $current = $pl->findByIdForCompany($listId, $cid);
        if ($current === null) {
            $listId = $defaultId;
        }
        $rows = $pl->productsWithPrices($cid, $listId);

        return $this->view('produtos/valores_venda', [
            'title' => 'Valores de venda',
            'pageTitle' => 'Valores de venda',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Valores de venda', 'href' => null],
            ],
            'priceLists' => $lists,
            'listId' => $listId,
            'rows' => $rows,
        ]);
    }

    public function valoresVendaSave(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos/valores-venda');
        }
        $pl = new PriceListRepository();
        $pl->ensureDefault($cid);
        $action = (string) $request->input('_action', 'save_prices');

        if ($action === 'create_list') {
            $name = trim((string) $request->input('new_list_name', ''));
            if ($name === '') {
                Session::flash('error', 'Informe o nome da nova tabela de preço.');
                redirect('/produtos/valores-venda');
            }
            $newId = $pl->createList($cid, $name);
            Session::flash('success', 'Tabela de preço criada.');
            redirect('/produtos/valores-venda?list_id=' . $newId);
        }

        if ($action === 'set_default') {
            $listId = (int) $request->input('list_id', 0);
            if ($listId < 1 || $pl->findByIdForCompany($listId, $cid) === null) {
                Session::flash('error', 'Tabela inválida.');
                redirect('/produtos/valores-venda');
            }
            $pl->setDefault($listId, $cid);
            Session::flash('success', 'Tabela padrão atualizada.');
            redirect('/produtos/valores-venda?list_id=' . $listId);
        }

        $listId = (int) $request->input('list_id', 0);
        if ($listId < 1 || $pl->findByIdForCompany($listId, $cid) === null) {
            Session::flash('error', 'Tabela de preço inválida.');
            redirect('/produtos/valores-venda');
        }

        $prices = $request->input('prices', []);
        if (!is_array($prices)) {
            $prices = [];
        }
        $prodRepo = new ProductRepository();
        foreach ($prices as $pidRaw => $rawPrice) {
            $pid = (int) $pidRaw;
            if ($pid < 1) {
                continue;
            }
            if ($prodRepo->findByIdForCompany($pid, $cid) === null) {
                continue;
            }
            $p = trim((string) $rawPrice);
            $p = str_replace(',', '.', $p);
            if ($p === '' || !is_numeric($p)) {
                continue;
            }
            $pl->upsertItem($listId, $pid, (string) round((float) $p, 4));
        }
        Session::flash('success', 'Preços da tabela atualizados.');
        redirect('/produtos/valores-venda?list_id=' . $listId);
    }

    public function etiquetas(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $repo = new ProductRepository();
        $result = $repo->paginate($cid, $q, '1', null, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('produtos/etiquetas', [
            'title' => 'Etiquetas',
            'pageTitle' => 'Etiquetas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Etiquetas', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'basePath' => '/produtos/etiquetas',
            'queryParams' => array_filter(['q' => $q !== '' ? $q : null]),
        ]);
    }

    public function etiquetasImprimir(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos/etiquetas');
        }
        $qty = $request->input('qty', []);
        if (!is_array($qty)) {
            $qty = [];
        }
        $ids = [];
        foreach ($qty as $pidRaw => $n) {
            $pid = (int) $pidRaw;
            $c = max(0, (int) $n);
            if ($pid > 0 && $c > 0) {
                $ids[$pid] = $c;
            }
        }
        if ($ids === []) {
            Session::flash('error', 'Selecione quantidades maiores que zero para ao menos um produto.');
            redirect('/produtos/etiquetas');
        }
        $repo = new ProductRepository();
        $idList = array_keys($ids);
        $products = $repo->findByIdsForCompany($idList, $cid);
        $lines = [];
        foreach ($products as $p) {
            $pid = (int) $p['id'];
            $lines[] = [
                'product' => $p,
                'qty' => $ids[$pid] ?? 1,
            ];
        }

        return $this->view('produtos/etiquetas_print', [
            'title' => 'Impressão de etiquetas',
            'lines' => $lines,
        ], null);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        $cid = $this->requireCompany();
        $tab = (string) $request->input('tab', 'categorias');
        if (!in_array($tab, ['categorias', 'marcas', 'unidades'], true)) {
            $tab = 'categorias';
        }
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $cat = new ProductCatalogRepository();

        $categories = ['rows' => [], 'total' => 0];
        $brands = ['rows' => [], 'total' => 0];
        $units = ['rows' => [], 'total' => 0];

        if ($tab === 'categorias') {
            $categories = $cat->categoriesPaginate($cid, $q, $page, self::CATALOG_PER_PAGE);
        } elseif ($tab === 'marcas') {
            $brands = $cat->brandsPaginate($cid, $q, $page, self::CATALOG_PER_PAGE);
        } else {
            $units = $cat->unitsPaginate($cid, $q, $page, self::CATALOG_PER_PAGE);
        }

        $totalForTab = $tab === 'categorias' ? $categories['total'] : ($tab === 'marcas' ? $brands['total'] : $units['total']);
        $totalPages = $totalForTab > 0 ? max(1, (int) ceil($totalForTab / self::CATALOG_PER_PAGE)) : 1;

        return $this->view('produtos/opcoes_auxiliares', [
            'title' => 'Opções auxiliares — Produtos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'tab' => $tab,
            'search' => $q,
            'page' => $page,
            'total' => $totalForTab,
            'totalPages' => $totalPages,
            'categoriesRows' => $categories['rows'],
            'brandsRows' => $brands['rows'],
            'unitsRows' => $units['rows'],
            'basePath' => '/produtos/opcoes-auxiliares',
            'queryParams' => array_filter([
                'tab' => $tab,
                'q' => $q !== '' ? $q : null,
            ]),
        ]);
    }

    public function opcoesCatalogoPost(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/produtos/opcoes-auxiliares');
        }
        $cat = new ProductCatalogRepository();
        $action = (string) $request->input('_action', '');
        $tab = (string) $request->input('tab', 'categorias');
        $redir = '/produtos/opcoes-auxiliares?tab=' . rawurlencode($tab);

        if ($action === 'create_category') {
            $name = trim((string) $request->input('name', ''));
            if ($name === '') {
                Session::flash('error', 'Informe o nome da categoria.');
                redirect($redir);
            }
            $slug = trim((string) $request->input('slug', ''));
            if ($slug === '') {
                $slug = lumis_slugify($name);
            }
            if ($cat->categorySlugExists($cid, $slug, null)) {
                Session::flash('error', 'Já existe uma categoria com este slug.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->insertCategory($cid, $name, $slug, $st);
            Session::flash('success', 'Categoria criada.');
            redirect($redir);
        }

        if ($action === 'update_category') {
            $id = (int) $request->input('id', 0);
            $row = $id > 0 ? $cat->findCategory($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Categoria não encontrada.');
                redirect($redir);
            }
            $name = trim((string) $request->input('name', ''));
            $slug = trim((string) $request->input('slug', ''));
            if ($name === '' || $slug === '') {
                Session::flash('error', 'Nome e slug são obrigatórios.');
                redirect($redir);
            }
            if ($cat->categorySlugExists($cid, $slug, $id)) {
                Session::flash('error', 'Slug já em uso por outra categoria.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->updateCategory($id, $cid, $name, $slug, $st);
            Session::flash('success', 'Categoria atualizada.');
            redirect($redir);
        }

        if ($action === 'delete_category') {
            $id = (int) $request->input('id', 0);
            $rowCat = $id > 0 ? $cat->findCategory($id, $cid) : null;
            if ($rowCat === null) {
                Session::flash('error', 'Categoria não encontrada.');
                redirect($redir);
            }
            if ($cat->countProductsUsingCategory($id, $cid) > 0) {
                Session::flash('error', 'Não é possível inativar: existem produtos vinculados a esta categoria.');
                redirect($redir);
            }
            $cat->updateCategory($id, $cid, (string) $rowCat['name'], (string) $rowCat['slug'], 0);
            Session::flash('success', 'Categoria inativada.');
            redirect($redir);
        }

        if ($action === 'create_brand') {
            $name = trim((string) $request->input('name', ''));
            if ($name === '') {
                Session::flash('error', 'Informe o nome da marca.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->insertBrand($cid, $name, $st);
            Session::flash('success', 'Marca criada.');
            redirect($redir);
        }

        if ($action === 'update_brand') {
            $id = (int) $request->input('id', 0);
            $row = $id > 0 ? $cat->findBrand($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Marca não encontrada.');
                redirect($redir);
            }
            $name = trim((string) $request->input('name', ''));
            if ($name === '') {
                Session::flash('error', 'Informe o nome da marca.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->updateBrand($id, $cid, $name, $st);
            Session::flash('success', 'Marca atualizada.');
            redirect($redir);
        }

        if ($action === 'delete_brand') {
            $id = (int) $request->input('id', 0);
            $row = $id > 0 ? $cat->findBrand($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Marca não encontrada.');
                redirect($redir);
            }
            if ($cat->countProductsUsingBrand($id, $cid) > 0) {
                Session::flash('error', 'Não é possível inativar: existem produtos vinculados a esta marca.');
                redirect($redir);
            }
            $cat->updateBrand($id, $cid, (string) $row['name'], 0);
            Session::flash('success', 'Marca inativada.');
            redirect($redir);
        }

        if ($action === 'create_unit') {
            $name = trim((string) $request->input('name', ''));
            $abbr = trim((string) $request->input('abbreviation', ''));
            if ($name === '' || $abbr === '') {
                Session::flash('error', 'Nome e abreviação são obrigatórios.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->insertUnit($cid, $name, $abbr, $st);
            Session::flash('success', 'Unidade criada.');
            redirect($redir);
        }

        if ($action === 'update_unit') {
            $id = (int) $request->input('id', 0);
            $row = $id > 0 ? $cat->findUnit($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Unidade não encontrada.');
                redirect($redir);
            }
            $name = trim((string) $request->input('name', ''));
            $abbr = trim((string) $request->input('abbreviation', ''));
            if ($name === '' || $abbr === '') {
                Session::flash('error', 'Nome e abreviação são obrigatórios.');
                redirect($redir);
            }
            $st = (int) $request->input('status', 1) === 0 ? 0 : 1;
            $cat->updateUnit($id, $cid, $name, $abbr, $st);
            Session::flash('success', 'Unidade atualizada.');
            redirect($redir);
        }

        if ($action === 'delete_unit') {
            $id = (int) $request->input('id', 0);
            $row = $id > 0 ? $cat->findUnit($id, $cid) : null;
            if ($row === null) {
                Session::flash('error', 'Unidade não encontrada.');
                redirect($redir);
            }
            if ($cat->countProductsUsingUnit($id, $cid) > 0) {
                Session::flash('error', 'Não é possível inativar: existem produtos vinculados a esta unidade.');
                redirect($redir);
            }
            $cat->updateUnit($id, $cid, (string) $row['name'], (string) $row['abbreviation'], 0);
            Session::flash('success', 'Unidade inativada.');
            redirect($redir);
        }

        Session::flash('error', 'Ação inválida.');
        redirect($redir);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractProductPayload(Request $request): array
    {
        return [
            'name' => trim((string) $request->input('name', '')),
            'sku' => trim((string) $request->input('sku', '')),
            'internal_code' => trim((string) $request->input('internal_code', '')),
            'barcode' => trim((string) $request->input('barcode', '')),
            'category_id' => $request->input('category_id', ''),
            'brand_id' => $request->input('brand_id', ''),
            'unit_id' => $request->input('unit_id', ''),
            'cost' => trim((string) $request->input('cost', '0')),
            'sale_price' => trim((string) $request->input('sale_price', '0')),
            'stock_qty' => trim((string) $request->input('stock_qty', '0')),
            'stock_min' => trim((string) $request->input('stock_min', '0')),
            'description' => trim((string) $request->input('description', '')),
            'status' => (int) $request->input('status', 1) === 0 ? 0 : 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateProduct(array $data, ProductRepository $repo, int $companyId, bool $isEdit, ?int $id): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Informe o nome do produto.';
        }
        if ($data['sku'] === '') {
            $errors['sku'] = 'Informe o SKU.';
        } elseif ($repo->skuExists($companyId, $data['sku'], $id)) {
            $errors['sku'] = 'Este SKU já está em uso.';
        }

        foreach (['cost' => 'custo', 'sale_price' => 'preço de venda', 'stock_qty' => 'estoque', 'stock_min' => 'estoque mínimo'] as $key => $label) {
            $raw = (string) ($data[$key] ?? '0');
            $raw = str_replace(',', '.', $raw);
            if ($raw === '' || !is_numeric($raw)) {
                $errors[$key] = 'Informe um valor numérico válido (' . $label . ').';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeProductForDb(array $data): array
    {
        $n = static fn (?string $s): ?string => $s === '' ? null : $s;
        $f = static fn (string $s): string => (string) (float) str_replace(',', '.', $s);

        $cat = $data['category_id'];
        $brand = $data['brand_id'];
        $unit = $data['unit_id'];

        return [
            'category_id' => $cat === '' || $cat === null ? null : (int) $cat,
            'brand_id' => $brand === '' || $brand === null ? null : (int) $brand,
            'unit_id' => $unit === '' || $unit === null ? null : (int) $unit,
            'name' => $data['name'],
            'sku' => $data['sku'],
            'internal_code' => $n($data['internal_code'] ?? null),
            'barcode' => $n($data['barcode'] ?? null),
            'cost' => $f((string) ($data['cost'] ?? '0')),
            'sale_price' => $f((string) ($data['sale_price'] ?? '0')),
            'stock_qty' => $f((string) ($data['stock_qty'] ?? '0')),
            'stock_min' => $f((string) ($data['stock_min'] ?? '0')),
            'description' => $n($data['description'] ?? null),
            'status' => $data['status'],
        ];
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
