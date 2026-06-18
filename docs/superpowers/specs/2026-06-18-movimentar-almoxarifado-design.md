# Design: Transferência de Produto entre Almoxarifados

**Data:** 2026-06-18
**Feature:** Movimentar produto para outro almoxarifado (tipo `transferencia`)

---

## Contexto

O SIPROQUIM registra movimentações de produtos em `movimentos` com dois tipos: `entrada` e `saida`. A nova feature adiciona um terceiro tipo, `transferencia`, que move estoque de um almoxarifado (origem) para outro (destino) em um único registro.

---

## Schema

### Alteração na tabela `movimentos`

Adicionar coluna `id_lugar_destino`:

```sql
ALTER TABLE movimentos
  ADD COLUMN IF NOT EXISTS id_lugar_destino INTEGER REFERENCES lugares(id) ON DELETE SET NULL;
```

- `NULL` para `tipo = 'entrada'` e `tipo = 'saida'` (comportamento atual preservado).
- Preenchido apenas para `tipo = 'transferencia'`.
- A migration segue o padrão existente em `scripts/init-db.sh` dentro do bloco `DO $$ ... $$`.

---

## Formulário (`src/cadastros/movimento.php`)

### Novo card "Movimentar"

Adicionado ao `tipo-selector` com visual de outline verde (consistente com o screenshot existente). Três cards: Entrada (verde), Saída (vermelho), Movimentar (outline verde).

### Comportamento dinâmico via JS

Quando `selectTipo('transferencia')` é chamado:
- Label "Almoxarifado:" muda para "Almoxarifado de origem:".
- Um segundo select "Almoxarifado de destino:" aparece ao lado na mesma `form-row` (toggle `display: none` → visível).
- `<input type="hidden" name="id_lugar_destino">` é submetido com o valor do select de destino.

Quando `entrada` ou `saida` é selecionado:
- Select de destino some (`display: none`).
- Label volta para "Almoxarifado:".
- `id_lugar_destino` hidden fica vazio.

### Validações no POST

| Condição | Mensagem de erro |
|---|---|
| `tipo == 'transferencia'` e `id_lugar_destino` vazio | "O almoxarifado de destino é obrigatório." |
| `id_lugar == id_lugar_destino` | "O almoxarifado de origem e destino não podem ser iguais." |
| Saldo em `id_lugar` (origem) < `quantidade` | "Estoque insuficiente. Saldo atual: {N}." |

### Inserção no banco

```sql
INSERT INTO movimentos
  (id_produto, id_pessoa, id_lugar, tipo, quantidade, data_movimento, observacao, id_lugar_destino)
VALUES
  (:id_produto, :id_pessoa, :id_lugar, 'transferencia', :quantidade, :data_movimento, :observacao, :id_lugar_destino)
```

Para `entrada`/`saida`, `id_lugar_destino` é inserido como `NULL` (sem alteração na lógica existente).

---

## Relatórios (`src/relatorios/relatorio_movimentos.php`)

### Query SQL

Adicionar `LEFT JOIN` para trazer o nome do almoxarifado de destino:

```sql
LEFT JOIN lugares ld ON ld.id = m.id_lugar_destino
```

Selecionar também `ld.nome AS lugar_destino`.

### Coluna "Local" na tabela

- `entrada` / `saida`: exibe `$mov['lugar']` (sem mudança).
- `transferencia`: exibe `"Almox A → Almox B"` (origem → destino).

### Coluna "Tipo"

```php
if ($mov['tipo'] == 'entrada') {
    // <span class="text-success">Entrada</span>
} elseif ($mov['tipo'] == 'saida') {
    // <span class="text-danger">Saída</span>
} else {
    // <span class="text-primary">Transferência</span>
}
```

### Cards de estatísticas

Adicionar card "Transferências" com contador próprio. Transferências não são somadas a entradas nem saídas.

---

## Arquivos afetados

| Arquivo | Tipo de mudança |
|---|---|
| `scripts/init-db.sh` | `ALTER TABLE` para adicionar `id_lugar_destino` |
| `src/cadastros/movimento.php` | Novo card, JS dinâmico, validação POST, INSERT atualizado |
| `src/relatorios/relatorio_movimentos.php` | JOIN, exibição de tipo/local, card de estatística |

---

## Fora do escopo

- Outros relatórios (`movimentacao_por_almoxarifado.php`, `produtos_por_local.php`) — não são afetados nesta entrega.
- Cálculo de estoque: transferência não altera o estoque total do sistema, apenas redistribui entre almoxarifados. Queries de saldo que somam `entrada - saida` por `id_lugar` permanecem corretas pois `transferencia` não é nem `entrada` nem `saida`.
