# Emissão fiscal real — Lumis ERP (NFe / NFCe / NFSe)

Este documento consolida a **ETAPA 1** (arquitetura, bibliotecas, modelagem, plano de integração) e orienta as etapas seguintes até emissão em produção.

## 1. Princípios

- **Não duplicar lógica fiscal fora de services**: Controllers apenas orquestram; `App\Services\Fiscal\*` concentra regras, montagem de XML/payload, assinatura, transmissão, persistência e logs.
- **Multiempresa / multiloja**: configuração fiscal por `company_id`; numeração e CSC podem variar por `store_id` quando necessário (`fiscal_series`).
- **Homologação e produção**: `tpAmb` (1 = produção, 2 = homologação) por empresa em `company_fiscal_settings`, nunca hardcoded.
- **Rastreabilidade**: todo envio/recebimento relevante gera linha em `fiscal_transmission_logs`; eventos de autorização/cancelamento em `fiscal_document_events`.
- **Reforma tributária (2026+)**: campos `reform_tax_json` (documento e linha) e extensões futuras no motor (`TaxReformContext`) sem quebrar emissão atual.

## 2. Escopo por documento

| Documento | Modelo | Integração primária | Biblioteca alvo |
|-----------|--------|---------------------|-----------------|
| NF-e produto | 55 | SEFAZ (SOAP) autorização / status / eventos | `nfephp-org/sped-nfe` + `nfephp-org/sped-common` |
| NFC-e produto | 65 | Idem + QR Code / CSC | Idem |
| NFS-e serviço | municipal / nacional | **Driver configurável**: API nacional (DPS) quando disponível no município; senão adaptador HTTP + credenciais por cidade | HTTP client + contratos `NfseDriverInterface`; sem pacote único obrigatório |

## 3. Bibliotecas (Composer)

| Pacote | Função |
|--------|--------|
| `nfephp-org/sped-nfe` (^5) | Geração de XML, assinatura, SOAP com SEFAZ, NFC-e, eventos (incl. cancelamento/inutilização conforme API da lib). |
| `nfephp-org/sped-common` | Dependência: certificado, XML, validações auxiliares. |
| `dompdf/dompdf` (já no projeto) | DANFE/PDF auxiliar em fases posteriores (ou render via NFePHP se usar fluxo recomendado pela lib). |

**Extensões PHP (servidor)**: `openssl`, `soap`, `dom`, `simplexml`, `json`, `zlib`, `mbstring`, `curl` (e `gd`/`zip` recomendados para DANFE/compactação).

**NFS-e**: não há pacote único oficial; o Lumis expõe `NfseEmissionService` com drivers. O driver “nacional” evoluirá conforme normas do município/API padrão.

## 4. Modelagem de dados (resumo)

- **`company_fiscal_settings`**: CRT, `tpAmb`, IBGE município emitente, CSC/CSC Id (NFC-e), certificado ativo (`digital_certificate_id`), flags NFSe (`nfse_integration_mode`, `nfse_endpoint`, metadados JSON).
- **`fiscal_series`**: sequência por empresa/loja/modelo/ambiente (série + próximo número com transação e lock na emissão).
- **`fiscal_documents`** (ALTER): modelo, ambiente, protocolo, recibo SEFAZ, XML assinado/autorizado, PDF, códigos de retorno, JSON reforma.
- **`fiscal_document_lines`** (ALTER): NCM, CFOP, CST/CSOSN, origem, CEST, EAN, unidade tributável, JSON de impostos/reforma.
- **`fiscal_transmission_logs`**: payload request/response (XML ou texto) por fase (`sign`, `transmit`, `retConsult`, etc.).
- **`fiscal_document_events`**: cancelamento, CC-e, inutilização, manifestação quando aplicável.
- **Cadastros**: `clients` / `products` / `services` — colunas fiscais adicionadas na migration 012 (IBGE, indicadores, NCM, lista serviço, etc.).

## 5. Fluxo NF-e / NFC-e (visão técnica)

1. **Pré-emissão**: validar emitente (perfil + certificado), destinatário, itens e totais (`FiscalValidationService` — ETAPA 3).
2. **Numeração**: reservar número em `fiscal_series` (transação).
3. **XML**: montar NFe com `sped-nfe` (Make) conforme layout vigente.
4. **Assinatura**: certificado A1 (PKCS12 já suportado no cadastro) via biblioteca.
5. **Transmissão**: SOAP SEFAZ UF; gravar request/response em `fiscal_transmission_logs`.
6. **Processamento**: retorno síncrono ou consulta recibo (`retConsReciNFe`); atualizar `fiscal_documents` e salvar XML autorizado em disco (`storage/fiscal/{company}/{year}/{model}/`).
7. **PDF**: DANFE (ETAPA 3/5).
8. **Cancelamento / inutilização**: eventos SEFAZ + persistência em `fiscal_document_events`.

## 6. Fluxo NFS-e (visão técnica)

1. Montar DPS/payload conforme driver (município ou nacional).
2. `POST` HTTPS com certificado se exigido.
3. Gravar retorno, número oficial, protocolo, XML/JSON e PDF quando houver.
4. Cancelamento via método do driver quando suportado.

## 7. Segurança

- Certificado: já criptografado (`password_encrypted`); arquivos `.pfx` fora de `public/`.
- Logs: não expor XML completo na UI para usuário sem permissão `notas_fiscais.logs.view`.
- Permissões finas por ação (emitir, cancelar, logs, certificado).

## 8. ETAPA 2 (base no repositório)

- Migration `012_fiscal_issuer_foundation.sql`: `company_fiscal_settings`, `fiscal_series`, `fiscal_transmission_logs`, `fiscal_document_events`, colunas em `fiscal_documents` / `fiscal_document_lines` e cadastros (`clients`, `products`, `services`).
- `config/fiscal.php` e Composer: `nfephp-org/sped-common`, `nfephp-org/sped-nfe` (no servidor/CI habilitar **ext-soap**; em ambiente sem SOAP, `composer update --ignore-platform-req=ext-soap`).
- Repositórios: `CompanyFiscalSettingsRepository`, `FiscalSeriesRepository`, `FiscalTransmissionLogRepository`, `FiscalDocumentEventRepository`; `FiscalDocumentRepository` estendido para campos fiscais e linhas com NCM/CFOP/etc.
- Services em `App\Services\Fiscal\*`: config, armazenamento, certificado, transmissão, eventos, impostos (placeholders reforma), stubs NFe/NFCe/NFSe/DANFE/XML.
- Permissões: `notas_fiscais.produtos.emitir|cancelar|consultar`, `notas_fiscais.servicos.emitir|cancelar|consultar`, `notas_fiscais.configurar`, `notas_fiscais.certificado.view`, `notas_fiscais.logs.view` — executar `php database/sync_permissions.php` após aplicar migration.

## 9. Próximas etapas no repositório

- **ETAPA 3**: `NfeEmissionService` concreto com `sped-nfe`, primeiro emit homologação.
- **ETAPA 4**: driver NFS-e + config por cidade.
- **ETAPA 5**: telas operacionais (emitir, reconsultar, cancelar, downloads), mensagens amigáveis + detalhe técnico.

---

*Documento gerado como parte da base fiscal do Lumis — manter alinhado ao código em `App\Services\Fiscal\*` e `database/migrations/012_fiscal_issuer_foundation.sql`.*
