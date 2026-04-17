<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ProductRepository;

final class ProdutosController extends Controller
{
    use RendersModulePlaceholder;

    private const PER_PAGE = 15;

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
        return $this->modulePlaceholder([
            'title' => 'Valores de venda',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Valores de venda', 'href' => null],
            ],
            'description' => 'Tabelas de preço, regras por canal, descontos e vigência — integradas ao catálogo e ao PDV.',
            'icon' => 'bi-currency-dollar',
        ]);
    }

    public function etiquetas(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Etiquetas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Etiquetas', 'href' => null],
            ],
            'description' => 'Layouts de etiquetas, impressão em lote e códigos de barras para gôndola e expedição.',
            'icon' => 'bi-printer',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Produtos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Marcas, famílias, unidades de medida e demais tabelas de apoio ao catálogo.',
            'icon' => 'bi-sliders',
        ]);
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
